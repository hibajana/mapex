@extends('layouts.app')

@section('title', 'Planifier votre circuit — MarrakechAI')

@section('content')
<div
    x-data="plannerApp()"
    class="max-w-4xl mx-auto px-4 py-12"
>
    <!-- Hero header -->
    <div class="text-center mb-12">
        <p class="text-xs tracking-[0.25em] uppercase text-[#C2714F] mb-3 font-medium">Assistant IA personnel</p>
        <h1 class="font-display text-5xl font-semibold text-[#1A1A2E] mb-4 leading-tight">
            Votre circuit<br><span class="text-[#C2714F]">sur mesure</span>
        </h1>
        <p class="text-gray-500 max-w-md mx-auto leading-relaxed">
            Renseignez vos préférences et contraintes de santé. L'IA construira un circuit optimisé, écologique et adapté.
        </p>
    </div>

    <!-- Loading overlay -->
    <div x-show="loading" x-cloak class="fixed inset-0 z-50 bg-[#1A1A2E]/90 flex flex-col items-center justify-center">
        <div class="text-center">
            <!-- Animated circles -->
            <div class="flex items-center justify-center gap-2 mb-6">
                <div class="w-3 h-3 rounded-full bg-[#C2714F] animate-bounce" style="animation-delay:0s"></div>
                <div class="w-3 h-3 rounded-full bg-[#D4A857] animate-bounce" style="animation-delay:0.15s"></div>
                <div class="w-3 h-3 rounded-full bg-[#6B7C4B] animate-bounce" style="animation-delay:0.3s"></div>
            </div>
            <p class="font-display text-2xl text-white mb-2">Gemini construit votre circuit…</p>
            <p class="text-sm text-gray-400" x-text="loadingMessage"></p>
        </div>
    </div>

    <!-- Form -->
    <form @submit.prevent="generate" class="space-y-8" x-show="!loading && !result">

        <!-- Section 1: Séjour -->
        <div class="bg-white rounded-2xl border border-[#E8E0D0] p-6 shadow-sm">
            <h2 class="font-display text-2xl font-semibold mb-5 flex items-center gap-2">
                <span class="w-7 h-7 rounded-full bg-[#F5EDD6] text-[#C2714F] text-sm font-bold flex items-center justify-center">1</span>
                Votre séjour
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Duration -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durée du séjour</label>
                    <div class="flex items-center gap-3">
                        <input
                            type="range" min="1" max="7" x-model="form.duration_days"
                            class="flex-1 accent-[#C2714F]"
                        >
                        <span class="font-display text-2xl font-semibold text-[#C2714F] w-16 text-center" x-text="form.duration_days + ' j'"></span>
                    </div>
                </div>

                <!-- Mode -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mode d'affichage</label>
                    <div class="flex gap-2">
                        <button type="button"
                            @click="form.mode = 'simple'"
                            :class="form.mode === 'simple' ? 'bg-[#C2714F] text-white border-[#C2714F]' : 'bg-white text-gray-600 border-[#E8E0D0]'"
                            class="flex-1 py-2 rounded-xl border text-sm font-medium transition-all">
                            Résumé
                        </button>
                        <button type="button"
                            @click="form.mode = 'detailed'"
                            :class="form.mode === 'detailed' ? 'bg-[#C2714F] text-white border-[#C2714F]' : 'bg-white text-gray-600 border-[#E8E0D0]'"
                            class="flex-1 py-2 rounded-xl border text-sm font-medium transition-all">
                            Détaillé
                        </button>
                    </div>
                </div>

                <!-- Budget -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Budget</label>
                    <select x-model="form.budget" class="w-full border border-[#E8E0D0] rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#C2714F]/30">
                        <option value="budget">Économique</option>
                        <option value="moderate">Modéré</option>
                        <option value="luxury">Luxe</option>
                    </select>
                </div>

                <!-- Comfort -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Niveau de confort</label>
                    <select x-model="form.comfort_level" class="w-full border border-[#E8E0D0] rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#C2714F]/30">
                        <option value="basic">Simple</option>
                        <option value="standard">Standard</option>
                        <option value="premium">Premium</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 2: Préférences touristiques -->
        <div class="bg-white rounded-2xl border border-[#E8E0D0] p-6 shadow-sm">
            <h2 class="font-display text-2xl font-semibold mb-5 flex items-center gap-2">
                <span class="w-7 h-7 rounded-full bg-[#F5EDD6] text-[#C2714F] text-sm font-bold flex items-center justify-center">2</span>
                Intérêts touristiques
            </h2>
            <p class="text-sm text-gray-500 mb-4">Sélectionnez au moins un centre d'intérêt.</p>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <template x-for="pref in preferenceOptions" :key="pref.value">
                    <button
                        type="button"
                        @click="togglePref(pref.value)"
                        :class="form.tourism_preferences.includes(pref.value)
                            ? 'bg-[#F5EDD6] border-[#C2714F] text-[#C2714F]'
                            : 'bg-white border-[#E8E0D0] text-gray-600'"
                        class="rounded-xl border p-3 text-left transition-all hover:border-[#C2714F]/50">
                        <div class="text-xl mb-1" x-text="pref.icon"></div>
                        <div class="text-xs font-medium" x-text="pref.label"></div>
                    </button>
                </template>
            </div>
        </div>

        <!-- Section 3: Santé -->
        <div class="bg-white rounded-2xl border border-[#E8E0D0] p-6 shadow-sm">
            <h2 class="font-display text-2xl font-semibold mb-5 flex items-center gap-2">
                <span class="w-7 h-7 rounded-full bg-[#F5EDD6] text-[#C2714F] text-sm font-bold flex items-center justify-center">3</span>
                Conditions de santé
            </h2>

            <div class="space-y-5">
                <!-- Toggles -->
                <div class="flex flex-col gap-4">
                    <label class="flex items-center justify-between cursor-pointer">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Mobilité réduite</p>
                            <p class="text-xs text-gray-400">Évite les escaliers, terrains irréguliers</p>
                        </div>
                        <div class="relative">
                            <input type="checkbox" x-model="form.reduced_mobility" class="sr-only">
                            <div @click="form.reduced_mobility = !form.reduced_mobility"
                                :class="form.reduced_mobility ? 'bg-[#C2714F]' : 'bg-gray-200'"
                                class="w-12 h-6 rounded-full cursor-pointer transition-colors relative">
                                <div :class="form.reduced_mobility ? 'translate-x-6' : 'translate-x-0'"
                                    class="w-6 h-6 bg-white rounded-full shadow absolute top-0 left-0 transition-transform"></div>
                            </div>
                        </div>
                    </label>

                    <label class="flex items-center justify-between cursor-pointer">
                        <div>
                            <p class="text-sm font-medium text-gray-700">Besoin d'ombre</p>
                            <p class="text-xs text-gray-400">Privilégie les souks couverts et jardins</p>
                        </div>
                        <div class="relative">
                            <input type="checkbox" x-model="form.needs_shade" class="sr-only">
                            <div @click="form.needs_shade = !form.needs_shade"
                                :class="form.needs_shade ? 'bg-[#C2714F]' : 'bg-gray-200'"
                                class="w-12 h-6 rounded-full cursor-pointer transition-colors relative">
                                <div :class="form.needs_shade ? 'translate-x-6' : 'translate-x-0'"
                                    class="w-6 h-6 bg-white rounded-full shadow absolute top-0 left-0 transition-transform"></div>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Endurance -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Endurance physique</label>
                    <div class="flex gap-2">
                        <template x-for="opt in [{v:'low',l:'Faible',d:'≤2 km'},{v:'medium',l:'Moyenne',d:'≤5 km'},{v:'high',l:'Élevée',d:'10 km+'}]" :key="opt.v">
                            <button type="button"
                                @click="form.physical_endurance = opt.v"
                                :class="form.physical_endurance === opt.v ? 'bg-[#C2714F] text-white border-[#C2714F]' : 'bg-white text-gray-600 border-[#E8E0D0]'"
                                class="flex-1 py-2.5 px-2 rounded-xl border text-center transition-all">
                                <div class="text-xs font-semibold" x-text="opt.l"></div>
                                <div class="text-xs opacity-70" x-text="opt.d"></div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end">
            <button
                type="submit"
                :disabled="form.tourism_preferences.length === 0"
                class="px-8 py-4 bg-[#C2714F] text-white font-medium rounded-2xl hover:bg-[#A0522D] transition-all disabled:opacity-40 disabled:cursor-not-allowed text-base shadow-lg shadow-[#C2714F]/20">
                Générer mon circuit ✦
            </button>
        </div>
    </form>

    <!-- Error -->
    <div x-show="error" x-cloak class="mt-6 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700" x-text="error"></div>

</div>
@endsection

@push('scripts')
<script>
function plannerApp() {
    return {
        loading: false,
        result: null,
        error: null,
        loadingMessage: 'Analyse de vos préférences…',

        loadingMessages: [
            'Analyse de vos préférences…',
            'Évaluation des conditions de santé…',
            'Optimisation des distances…',
            'Construction du circuit écologique…',
            'Calcul du score environnemental…',
            'Finalisation de votre itinéraire…',
        ],

        form: {
            duration_days: 3,
            mode: 'simple',
            budget: 'moderate',
            comfort_level: 'standard',
            tourism_preferences: ['histoire'],
            reduced_mobility: false,
            needs_shade: false,
            physical_endurance: 'medium',
            allergies: [],
        },

        preferenceOptions: [
            { value: 'artisanat',       label: 'Artisanat',      icon: '🏺' },
            { value: 'jardins',         label: 'Jardins',        icon: '🌿' },
            { value: 'musées',          label: 'Musées',         icon: '🏛️' },
            { value: 'histoire',        label: 'Histoire',       icon: '🕌' },
            { value: 'gastronomie',     label: 'Gastronomie',    icon: '🫕' },
            { value: 'architecture',    label: 'Architecture',   icon: '✦' },
            { value: 'spiritualité',    label: 'Spiritualité',   icon: '🌙' },
            { value: 'quartiers_locaux', label: 'Vie locale',    icon: '🛖' },
        ],

        togglePref(val) {
            const idx = this.form.tourism_preferences.indexOf(val);
            if (idx >= 0) this.form.tourism_preferences.splice(idx, 1);
            else this.form.tourism_preferences.push(val);
        },

        async generate() {
            this.loading = true;
            this.error = null;
            let msgIdx = 0;

            // Cycle through loading messages
            const interval = setInterval(() => {
                msgIdx = (msgIdx + 1) % this.loadingMessages.length;
                this.loadingMessage = this.loadingMessages[msgIdx];
            }, 2000);

            try {
                const resp = await fetch('/api/generate-itinerary', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(this.form),
                });

                const data = await resp.json();

                if (!data.success) {
                    this.error = data.error || 'Une erreur est survenue.';
                } else {
                    // Redirect to itinerary view
                    window.location.href = `/itinerary/${data.itinerary.id}`;
                }
            } catch (e) {
                this.error = 'Erreur réseau. Vérifiez votre connexion.';
            } finally {
                clearInterval(interval);
                this.loading = false;
            }
        },
    }
}
</script>
@endpush
