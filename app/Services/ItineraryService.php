<?php

namespace App\Services;

use App\Models\Itinerary;
use App\Models\ItineraryPlace;
use App\Models\HealthConditionsHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItineraryService
{
    public function __construct(
        private GeminiService      $gemini,
        private PromptBuilderService $promptBuilder,
    ) {}

    /**
     * Main entry: generate and persist a new itinerary.
     */
    public function generate(int $userId, array $preferences, array $health, string $mode): Itinerary
    {
        $prompt = $this->promptBuilder->buildItineraryPrompt($preferences, $health, $mode);

        Log::info('Generating itinerary', ['user_id' => $userId, 'mode' => $mode, 'days' => $preferences['duration_days']]);

        $aiData = $this->gemini->generate($prompt);

        return DB::transaction(function () use ($userId, $preferences, $health, $mode, $aiData) {
            // Validate required AI output keys
            $this->validateAiResponse($aiData);

            $itinerary = Itinerary::create([
                'user_id'                    => $userId,
                'trip_title'                 => $aiData['trip_title'],
                'mode'                       => $mode,
                'eco_score'                  => min(100, max(0, (int) $aiData['eco_score'])),
                'comfort_score'              => min(100, max(0, (int) $aiData['comfort_score'])),
                'duration_days'              => $preferences['duration_days'],
                'user_preferences_snapshot'  => $preferences,
                'health_snapshot'            => $health,
            ]);

            foreach ($aiData['days'] as $dayData) {
                foreach ($dayData['places'] as $order => $place) {
                    ItineraryPlace::create([
                        'itinerary_id'       => $itinerary->id,
                        'day'                => $dayData['day'],
                        'order'              => $order + 1,
                        'name'               => $place['name'],
                        'description'        => $place['description'],
                        'reason_responsible' => $place['reason_responsible'] ?? null,
                        'health_reason'      => $place['health_reason'] ?? null,
                        'effort_level'       => $place['effort_level'] ?? 'medium',
                        'accessible'         => strtolower($place['accessibility'] ?? '') !== 'non accessible',
                        'lat'                => (float) $place['lat'],
                        'lng'                => (float) $place['lng'],
                        'image_query'        => $place['image_query'] ?? $place['name'],
                    ]);
                }
            }

            // Save health snapshot to history
            HealthConditionsHistory::create([
                'user_id'           => $userId,
                'itinerary_id'      => $itinerary->id,
                'reduced_mobility'  => $health['reduced_mobility'] ?? false,
                'needs_shade'       => $health['needs_shade'] ?? false,
                'physical_endurance' => $health['physical_endurance'] ?? 'medium',
                'allergies'         => $health['allergies'] ?? null,
            ]);

            return $itinerary->load('places');
        });
    }

    /**
     * Regenerate with relaxed mode (more rest, less walking).
     */
    public function regenerateRelaxed(Itinerary $itinerary): Itinerary
    {
        $prefs   = $itinerary->user_preferences_snapshot;
        $health  = array_merge($itinerary->health_snapshot, [
            'physical_endurance' => 'low',
            'needs_shade'        => true,
        ]);

        return $this->generate($itinerary->user_id, $prefs, $health, $itinerary->mode);
    }

    /**
     * Regenerate with eco-maximized mode.
     */
    public function regenerateEco(Itinerary $itinerary): Itinerary
    {
        $prefs  = array_merge($itinerary->user_preferences_snapshot, [
            'tourism_preferences' => ['artisanat', 'quartiers_locaux', 'marchés_authentiques'],
        ]);
        $health = $itinerary->health_snapshot;

        return $this->generate($itinerary->user_id, $prefs, $health, $itinerary->mode);
    }

    /**
     * Toggle between simple and detailed mode (re-generate with AI).
     */
    public function toggleMode(Itinerary $itinerary): Itinerary
    {
        $newMode = $itinerary->mode === 'simple' ? 'detailed' : 'simple';
        return $this->generate(
            $itinerary->user_id,
            $itinerary->user_preferences_snapshot,
            $itinerary->health_snapshot,
            $newMode
        );
    }

    private function validateAiResponse(array $data): void
    {
        $required = ['trip_title', 'eco_score', 'comfort_score', 'days'];
        foreach ($required as $key) {
            if (!array_key_exists($key, $data)) {
                throw new \InvalidArgumentException("Missing key in AI response: {$key}");
            }
        }
        if (empty($data['days']) || !is_array($data['days'])) {
            throw new \InvalidArgumentException("AI response has no days array");
        }
    }
}
