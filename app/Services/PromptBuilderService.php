<?php

namespace App\Services;

class PromptBuilderService
{
    /**
     * Build the full Gemini prompt from user preferences and health data.
     */
    public function buildItineraryPrompt(array $preferences, array $health, string $mode): string
    {
        $durationDays       = $preferences['duration_days'];
        $tourismPrefs       = implode(', ', $preferences['tourism_preferences'] ?? ['histoire', 'artisanat']);
        $budget             = $preferences['budget'] ?? 'moderate';
        $comfortLevel       = $preferences['comfort_level'] ?? 'standard';
        $reducedMobility    = $health['reduced_mobility'] ? 'OUI' : 'NON';
        $needsShade         = $health['needs_shade'] ? 'OUI' : 'NON';
        $endurance          = $health['physical_endurance'] ?? 'medium';
        $allergies          = !empty($health['allergies']) ? implode(', ', $health['allergies']) : 'aucune';
        $modeInstruction    = $mode === 'simple'
            ? 'Mode SIMPLE : 3 à 4 lieux max par jour, descriptions courtes (1-2 phrases).'
            : 'Mode DÉTAILLÉ : descriptions complètes, justifications écologiques et santé détaillées.';

        $enduranceMap = ['low' => 'faible (max 2-3km/jour)', 'medium' => 'moyenne (max 5km/jour)', 'high' => 'élevée (10km+ possibles)'];
        $enduranceLabel = $enduranceMap[$endurance] ?? 'moyenne';

        return <<<PROMPT
Tu es un expert en tourisme durable et santé à Marrakech, Maroc.

Génère un circuit touristique intelligent et optimisé pour un séjour de {$durationDays} jour(s).

## PROFIL UTILISATEUR
- Préférences touristiques : {$tourismPrefs}
- Budget : {$budget}
- Niveau de confort : {$comfortLevel}

## CONTRAINTES SANTÉ (OBLIGATOIRES à respecter)
- Mobilité réduite : {$reducedMobility} → si OUI, éviter absolument les escaliers et terrains irréguliers
- Besoin d'ombre : {$needsShade} → si OUI, privilégier les souks couverts, jardins ombragés, riads
- Endurance physique : {$enduranceLabel}
- Allergies déclarées : {$allergies}

## PRINCIPES ÉCOLOGIQUES (OBLIGATOIRES)
- Favoriser les artisans locaux plutôt que les chaînes touristiques
- Privilegier les transports doux (marche, calèche, vélo)
- Éviter les lieux trop surpeuplés pour une expérience authentique
- Recommander les petits commerces du quartier

## OPTIMISATION DU CIRCUIT
- Ordonner les lieux intelligemment pour minimiser les distances
- Grouper les lieux proches géographiquement dans la même demi-journée
- Planifier les visites les plus fatiguantes le matin (fraîcheur)
- Insérer des pauses ombragées si endurance faible

## INSTRUCTION MODE
{$modeInstruction}

## FORMAT DE RÉPONSE OBLIGATOIRE
Réponds UNIQUEMENT avec un objet JSON valide, sans texte avant ou après, sans balises markdown.
Utilise exactement ce schéma :

{
  "trip_title": "Titre poétique du circuit",
  "mode": "{$mode}",
  "eco_score": <entier 0-100>,
  "comfort_score": <entier 0-100>,
  "days": [
    {
      "day": 1,
      "theme": "Thème du jour (ex: Médina historique)",
      "places": [
        {
          "name": "Nom du lieu",
          "description": "Description engageante",
          "reason_responsible": "Pourquoi ce lieu est écologique/responsable",
          "health_reason": "Adaptation santé spécifique",
          "effort_level": "low|medium|high",
          "accessibility": "Description accessibilité (escaliers, sol, etc.)",
          "lat": <latitude précise>,
          "lng": <longitude précise>,
          "image_query": "query pour trouver une image (ex: Jardin Majorelle Marrakech)"
        }
      ]
    }
  ]
}

CRITIQUE : Les coordonnées lat/lng doivent être précises pour Marrakech (env. 31.6°N, 8.0°W).
PROMPT;
    }

    /**
     * Build regeneration prompt with fatigue context.
     */
    public function buildRegenPrompt(array $originalPrefs, array $health, int $dayNumber, string $reason): string
    {
        return <<<PROMPT
Le touriste se trouve actuellement au jour {$dayNumber} de son circuit à Marrakech.

Raison de l'adaptation : {$reason}

Génère uniquement le programme pour le reste du séjour en mode plus reposant.
Réponse en JSON valide avec le même schéma que précédemment, uniquement pour les jours restants.
PROMPT;
    }
}
