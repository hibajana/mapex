<?php
// config/services.php — ajoutez cette entrée dans votre tableau existant

return [
    // ... autres services (mail, etc.)

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
    ],
];

/*
|--------------------------------------------------------------------------
| app/Providers/AppServiceProvider.php — ajoutez dans register()
|--------------------------------------------------------------------------
|
|    $this->app->singleton(\App\Services\GeminiService::class);
|    $this->app->singleton(\App\Services\PromptBuilderService::class);
|    $this->app->singleton(\App\Services\ItineraryService::class);
|
|--------------------------------------------------------------------------
| app/Policies/ItineraryPolicy.php
|--------------------------------------------------------------------------
*/

namespace App\Policies;

use App\Models\Itinerary;
use App\Models\User;

class ItineraryPolicy
{
    public function view(User $user, Itinerary $itinerary): bool
    {
        return $user->id === $itinerary->user_id;
    }

    public function update(User $user, Itinerary $itinerary): bool
    {
        return $user->id === $itinerary->user_id;
    }
}
