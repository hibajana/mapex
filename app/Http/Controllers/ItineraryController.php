<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateItineraryRequest;
use App\Models\Itinerary;
use App\Services\ItineraryService;
use App\Exceptions\GeminiException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ItineraryController extends Controller
{
    public function __construct(private ItineraryService $itineraryService)
    {
        $this->middleware('auth');
    }

    /**
     * POST /generate-itinerary
     */
    public function generate(GenerateItineraryRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $preferences = [
                'duration_days'        => $data['duration_days'],
                'tourism_preferences'  => $data['tourism_preferences'],
                'budget'               => $data['budget'],
                'comfort_level'        => $data['comfort_level'],
            ];

            $health = [
                'reduced_mobility'   => $data['reduced_mobility'],
                'needs_shade'        => $data['needs_shade'],
                'physical_endurance' => $data['physical_endurance'],
                'allergies'          => $data['allergies'] ?? [],
            ];

            $itinerary = $this->itineraryService->generate(
                auth()->id(),
                $preferences,
                $health,
                $data['mode'] ?? 'simple'
            );

            return response()->json([
                'success'   => true,
                'itinerary' => $this->formatItinerary($itinerary),
            ]);

        } catch (GeminiException $e) {
            Log::error('Gemini error in generate', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error'   => 'Le service IA est temporairement indisponible. Veuillez réessayer dans quelques instants.',
            ], 503);
        } catch (\Throwable $e) {
            Log::error('Unexpected error in generate', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Une erreur inattendue est survenue.'], 500);
        }
    }

    /**
     * POST /regenerate-itinerary/{id}
     */
    public function regenerate(Request $request, Itinerary $itinerary): JsonResponse
    {
        $this->authorize('view', $itinerary);

        try {
            $newItinerary = $this->itineraryService->generate(
                auth()->id(),
                $itinerary->user_preferences_snapshot,
                $itinerary->health_snapshot,
                $itinerary->mode
            );

            return response()->json(['success' => true, 'itinerary' => $this->formatItinerary($newItinerary)]);
        } catch (GeminiException $e) {
            return response()->json(['success' => false, 'error' => 'Service IA indisponible.'], 503);
        }
    }

    /**
     * POST /relax-mode/{id}
     */
    public function relaxMode(Itinerary $itinerary): JsonResponse
    {
        $this->authorize('view', $itinerary);

        try {
            $newItinerary = $this->itineraryService->regenerateRelaxed($itinerary);
            return response()->json(['success' => true, 'itinerary' => $this->formatItinerary($newItinerary)]);
        } catch (GeminiException $e) {
            return response()->json(['success' => false, 'error' => 'Service IA indisponible.'], 503);
        }
    }

    /**
     * POST /eco-mode/{id}
     */
    public function ecoMode(Itinerary $itinerary): JsonResponse
    {
        $this->authorize('view', $itinerary);

        try {
            $newItinerary = $this->itineraryService->regenerateEco($itinerary);
            return response()->json(['success' => true, 'itinerary' => $this->formatItinerary($newItinerary)]);
        } catch (GeminiException $e) {
            return response()->json(['success' => false, 'error' => 'Service IA indisponible.'], 503);
        }
    }

    /**
     * GET /history
     */
    public function history(Request $request): JsonResponse
    {
        $itineraries = Itinerary::forUser(auth()->id())
            ->with('places')
            ->paginate(10);

        return response()->json([
            'success'     => true,
            'itineraries' => $itineraries,
        ]);
    }

    /**
     * GET /itinerary/{id} - for view page
     */
    public function show(Itinerary $itinerary)
    {
        $this->authorize('view', $itinerary);
        $itinerary->load('places');

        return view('itinerary.show', [
            'itinerary'    => $itinerary,
            'placesByDay'  => $itinerary->placesByDay(),
            'mapPlaces'    => $itinerary->places->map(fn($p) => [
                'name' => $p->name, 'lat' => $p->lat, 'lng' => $p->lng,
                'day' => $p->day, 'description' => $p->description,
                'effort' => $p->effort_level, 'accessible' => $p->accessible,
            ])->toArray(),
        ]);
    }

    private function formatItinerary(Itinerary $itinerary): array
    {
        return [
            'id'            => $itinerary->id,
            'trip_title'    => $itinerary->trip_title,
            'mode'          => $itinerary->mode,
            'eco_score'     => $itinerary->eco_score,
            'comfort_score' => $itinerary->comfort_score,
            'duration_days' => $itinerary->duration_days,
            'days'          => $itinerary->placesByDay()->map(fn($places, $day) => [
                'day'    => $day,
                'places' => $places->toArray(),
            ])->values(),
        ];
    }
}
