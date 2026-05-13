<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateItineraryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'duration_days'       => 'required|integer|min:1|max:14',
            'tourism_preferences' => 'required|array|min:1',
            'tourism_preferences.*' => 'string|in:artisanat,jardins,musées,histoire,gastronomie,architecture,spiritualité,quartiers_locaux',
            'reduced_mobility'    => 'required|boolean',
            'needs_shade'         => 'required|boolean',
            'physical_endurance'  => 'required|string|in:low,medium,high',
            'budget'              => 'required|string|in:budget,moderate,luxury',
            'comfort_level'       => 'required|string|in:basic,standard,premium',
            'allergies'           => 'nullable|array',
            'allergies.*'         => 'string|in:pollen,dust,animals,food,none',
            'mode'                => 'nullable|string|in:simple,detailed',
        ];
    }

    public function messages(): array
    {
        return [
            'duration_days.required'       => 'La durée du séjour est obligatoire.',
            'duration_days.max'            => 'La durée maximale est de 14 jours.',
            'tourism_preferences.required' => 'Sélectionnez au moins une préférence touristique.',
            'physical_endurance.in'        => 'Endurance invalide. Valeurs : low, medium, high.',
        ];
    }
}
