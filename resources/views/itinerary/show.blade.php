@extends('layouts.app')

@section('title', $itinerary->trip_title . ' — MarrakechAI')

@section('content')
<div x-data="itineraryApp()" class="max-w-7xl mx-auto px-4 py-8">

    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <p class="text-xs tracking-[0.2em] uppercase text-[#C2714F] mb-2">{{ $itinerary->duration_days }} jour(s) · Marrakech</p>
            <h1 class="font-display text-4xl font-semibold text-[#1A1A2E]">{{ $itinerary->trip_title }}</h1>
        </div>

        <!-- Scores -->
        <div class="flex gap-4">
            <div class="bg-white border border-[#E8E0D0] rounded-xl px-4 py-3 text-center min-w-[80px]">
                <div class="font-display text-2xl font-semibold text-[#6B7C4B]">{{ $itinerary->eco_score }}</div>
                <div class="text-xs text-gray-500 mt-0.5">Éco</div>
            </div>
            <div class="bg-white border border-[#E8E0D0] rounded-xl px-4 py-3 text-center min-w-[80px]">
                <div class="font-display text-2xl font-semibold text-[#C2714F]">{{ $itinerary->comfort_score }}</div>
                <div class="text-xs text-gray-500 mt-0.5">Confort</div>
            </div>
            <div class="bg-[#{{ $itinerary->eco_score >= 80 ? '6B7C4B' : ($itinerary->eco_score >= 60 ? 'D4A857' : 'C2714F') }}] rounded-xl px-4 py-3 text-center min-w-[100px]">
                <div class="font-display text-lg font-semibold text-white">{{ $itinerary->eco_score_label }}</div>
                <div class="text-xs text-white/80 mt-0.5">Niveau éco</div>
            </div>
        </div>
    </div>

    <!-- Mode toggle + Actions -->
    <div class="flex flex-wrap gap-3 mb-6">
        <!-- Simple / Détaillé -->
        <div class="flex gap-1 bg-white border border-[#E8E0D0] rounded-xl p-1">
            <button @click="mode='simple'"
                :class="mode==='simple' ? 'bg-[#C2714F] text-white' : 'text-gray-500'"
                class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all">
                Résumé
            </button>
            <button @click="mode='detailed'"
                :class="mode==='detailed' ? 'bg-[#C2714F] text-white' : 'text-gray-500'"
                class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all">
                Détaillé
            </button>
        </div>

        <!-- Adaptive actions -->
        <button @click="adaptRelax()"
            :disabled="adapting"
            class="flex items-center gap-2 px-4 py-2 bg-white border border-[#E8E0D0] rounded-xl text-sm font-medium text-gray-600 hover:border-[#C2714F] transition-all disabled:opacity-50">
            <span>😌</span> Mode reposant
        </button>
        <button @click="adaptEco()"
            :disabled="adapting"
            class="flex items-center gap-2 px-4 py-2 bg-white border border-[#E8E0D0] rounded-xl text-sm font-medium text-gray-600 hover:border-[#6B7C4B] transition-all disabled:opacity-50">
            <span>🌿</span> Mode éco+
        </button>
        <button @click="regenerate()"
            :disabled="adapting"
            class="flex items-center gap-2 px-4 py-2 bg-white border border-[#E8E0D0] rounded-xl text-sm font-medium text-gray-600 hover:border-[#D4A857] transition-all disabled:opacity-50">
            <span>🔄</span> Regénérer
        </button>

        <!-- Adapting spinner -->
        <div x-show="adapting" class="flex items-center gap-2 px-4 py-2 bg-[#F5EDD6] rounded-xl text-sm text-[#C2714F]">
            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z"/>
            </svg>
            L'IA adapte…
        </div>
    </div>

    <!-- Layout: Map + Circuit -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Carte Leaflet -->
        <div class="bg-white border border-[#E8E0D0] rounded-2xl overflow-hidden shadow-sm" style="height: 560px;">
            <div id="map" class="w-full h-full"></div>
        </div>

        <!-- Circuit jours -->
        <div class="space-y-4 overflow-y-auto" style="max-height: 560px;">

            @foreach($placesByDay as $day => $places)
            <div class="bg-white border border-[#E8E0D0] rounded-2xl overflow-hidden shadow-sm">

                <!-- Day header -->
                <div class="bg-gradient-to-r from-[#F5EDD6] to-[#FAF7F2] px-5 py-3 flex items-center justify-between">
                    <span class="font-display text-lg font-semibold text-[#1A1A2E]">Jour {{ $day }}</span>
                    <span class="text-xs text-[#C2714F] font-medium">{{ count($places) }} lieu(x)</span>
                </div>

                <!-- Places list -->
                <div class="divide-y divide-[#F0EAE0]">
                    @foreach($places as $idx => $place)
                    <div class="p-4 hover:bg-[#FAF7F2] transition-colors cursor-pointer place-card"
                        data-lat="{{ $place->lat }}" data-lng="{{ $place->lng }}" data-name="{{ $place->name }}">

                        <div class="flex items-start gap-3">
                            <!-- Order badge -->
                            <div class="w-6 h-6 rounded-full bg-[#C2714F] text-white text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">
                                {{ $idx + 1 }}
                            </div>

                            <div class="flex-1 min-w-0">
                                <!-- Name + effort badge -->
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-medium text-sm text-[#1A1A2E]">{{ $place->name }}</h3>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $place->effort_level === 'low' ? 'bg-green-100 text-green-700' : ($place->effort_level === 'high' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ ['low' => '🚶 Facile', 'medium' => '🏃 Modéré', 'high' => '⛰️ Intense'][$place->effort_level] ?? $place->effort_level }}
                                    </span>
                                    @if($place->accessible)
                                    <span class="text-xs text-blue-500">♿</span>
                                    @endif
                                </div>

                                <!-- Description (always shown) -->
                                <p class="text-xs text-gray-500 leading-relaxed">{{ $place->description }}</p>

                                <!-- Detailed mode extra info -->
                                <div x-show="mode === 'detailed'" class="mt-3 space-y-2">
                                    @if($place->reason_responsible)
                                    <div class="flex items-start gap-1.5 text-xs">
                                        <span class="text-green-500 mt-0.5">🌱</span>
                                        <span class="text-gray-600">{{ $place->reason_responsible }}</span>
                                    </div>
                                    @endif
                                    @if($place->health_reason)
                                    <div class="flex items-start gap-1.5 text-xs">
                                        <span class="text-blue-400 mt-0.5">🏥</span>
                                        <span class="text-gray-600">{{ $place->health_reason }}</span>
                                    </div>
                                    @endif
                                    <div class="flex items-start gap-1.5 text-xs">
                                        <span class="text-[#C2714F] mt-0.5">📍</span>
                                        <span class="text-gray-400 font-mono">{{ number_format($place->lat, 4) }}, {{ number_format($place->lng, 4) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Print / Save -->
    <div class="mt-6 flex gap-3">
        <a href="{{ route('history.index') }}" class="text-sm text-gray-500 hover:text-[#C2714F] transition-colors">← Voir l'historique</a>
        <button onclick="window.print()" class="ml-auto text-sm text-gray-500 hover:text-[#C2714F] transition-colors">🖨️ Imprimer</button>
    </div>

</div>
@endsection

@push('scripts')
<script>
// Map places data from PHP
const MAP_PLACES = @json($mapPlaces);
const ITINERARY_ID = {{ $itinerary->id }};

function itineraryApp() {
    return {
        mode: '{{ $itinerary->mode }}',
        adapting: false,
        map: null,

        async adaptRelax() {
            this.adapting = true;
            try {
                const resp = await fetch(`/api/relax-mode/${ITINERARY_ID}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const data = await resp.json();
                if (data.success) window.location.href = `/itinerary/${data.itinerary.id}`;
            } finally { this.adapting = false; }
        },

        async adaptEco() {
            this.adapting = true;
            try {
                const resp = await fetch(`/api/eco-mode/${ITINERARY_ID}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const data = await resp.json();
                if (data.success) window.location.href = `/itinerary/${data.itinerary.id}`;
            } finally { this.adapting = false; }
        },

        async regenerate() {
            this.adapting = true;
            try {
                const resp = await fetch(`/api/regenerate-itinerary/${ITINERARY_ID}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const data = await resp.json();
                if (data.success) window.location.href = `/itinerary/${data.itinerary.id}`;
            } finally { this.adapting = false; }
        },
    }
}

// Initialize Leaflet map
document.addEventListener('DOMContentLoaded', function () {
    const map = L.map('map');

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    if (!MAP_PLACES.length) return;

    const dayColors = ['#C2714F', '#6B7C4B', '#D4A857', '#8B6F47', '#5B8DB8'];
    const bounds = [];
    const dayMarkers = {};

    MAP_PLACES.forEach((place, idx) => {
        const day = place.day || 1;
        const color = dayColors[(day - 1) % dayColors.length];

        // Custom colored marker
        const icon = L.divIcon({
            className: '',
            html: `<div style="
                width:32px;height:32px;border-radius:50% 50% 50% 0;
                background:${color};border:2px solid white;
                box-shadow:0 2px 8px rgba(0,0,0,0.3);
                transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;
            "><span style="transform:rotate(45deg);color:white;font-size:11px;font-weight:700">
                ${idx + 1}
            </span></div>`,
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32],
        });

        const marker = L.marker([place.lat, place.lng], { icon })
            .addTo(map)
            .bindPopup(`
                <div style="font-family:sans-serif;min-width:180px">
                    <div style="font-weight:600;font-size:13px;margin-bottom:4px">${place.name}</div>
                    <div style="font-size:11px;color:#888;margin-bottom:6px">Jour ${day} · ${place.effort === 'low' ? '🚶 Facile' : place.effort === 'high' ? '⛰️ Intense' : '🏃 Modéré'}</div>
                    <p style="font-size:12px;color:#555;line-height:1.5;margin:0">${place.description.substring(0, 120)}…</p>
                    ${place.accessible ? '<div style="margin-top:4px;font-size:11px;color:#3B82F6">♿ Accessible</div>' : ''}
                </div>
            `);

        bounds.push([place.lat, place.lng]);
        if (!dayMarkers[day]) dayMarkers[day] = [];
        dayMarkers[day].push([place.lat, place.lng]);
    });

    // Draw polylines per day
    Object.entries(dayMarkers).forEach(([day, coords]) => {
        if (coords.length > 1) {
            const color = dayColors[(parseInt(day) - 1) % dayColors.length];
            L.polyline(coords, {
                color: color,
                weight: 2.5,
                opacity: 0.6,
                dashArray: '6, 6',
            }).addTo(map);
        }
    });

    // Fit map to all markers
    map.fitBounds(bounds, { padding: [40, 40] });

    // Click on place card → focus map
    document.querySelectorAll('.place-card').forEach(card => {
        card.addEventListener('click', () => {
            const lat = parseFloat(card.dataset.lat);
            const lng = parseFloat(card.dataset.lng);
            map.setView([lat, lng], 17, { animate: true });
        });
    });
});
</script>
@endpush
