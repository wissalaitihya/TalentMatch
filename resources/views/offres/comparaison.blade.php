<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Comparer des candidats') }} — {{ $offre->titre }}
            </h2>
            <a href="{{ route('offres.show', $offre) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Retour à l\'offre') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="px-4 py-3 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $recoColors = [
                    'convoquer' => 'bg-green-100 text-green-800',
                    'attente' => 'bg-orange-100 text-orange-800',
                    'rejeter' => 'bg-red-100 text-red-800',
                ];
                $recoLabels = [
                    'convoquer' => 'À convoquer',
                    'attente' => 'En attente',
                    'rejeter' => 'À rejeter',
                ];
            @endphp

            @if (! isset($analyse1) || ! isset($analyse2))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4">{{ __('Sélectionner deux candidats à comparer') }}</h3>

                        @php
                            $candidats = $offre->analyses->map(fn ($a) => $a->candidat)->filter();
                        @endphp

                        @if ($candidats->count() < 2)
                            <p class="text-gray-500">{{ __('Au moins deux candidats avec une analyse terminée sont requis pour la comparaison.') }}</p>
                        @else
                            <form method="POST" action="{{ route('offres.comparaison.compare', $offre) }}" class="space-y-4">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="candidat_id_1" class="block text-sm font-medium text-gray-700">{{ __('Premier candidat') }}</label>
                                        <select id="candidat_id_1" name="candidat_id_1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">{{ __('Sélectionner...') }}</option>
                                            @foreach ($candidats as $candidat)
                                                <option value="{{ $candidat->id }}" @selected(old('candidat_id_1') == $candidat->id)>
                                                    {{ $candidat->nom_candidat }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="candidat_id_2" class="block text-sm font-medium text-gray-700">{{ __('Deuxième candidat') }}</label>
                                        <select id="candidat_id_2" name="candidat_id_2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">{{ __('Sélectionner...') }}</option>
                                            @foreach ($candidats as $candidat)
                                                <option value="{{ $candidat->id }}" @selected(old('candidat_id_2') == $candidat->id)>
                                                    {{ $candidat->nom_candidat }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Comparer') }}
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-6">{{ __('Résultat de la comparaison') }}</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach ([
                                ['analyse' => $analyse1, 'num' => 1],
                                ['analyse' => $analyse2, 'num' => 2],
                            ] as $item)
                                @php
                                    $a = $item['analyse'];
                                    $candidat = $a->candidat;
                                @endphp
                                <div class="border rounded-lg p-4 {{ $item['num'] === 1 ? 'border-indigo-300' : 'border-teal-300' }}">
                                    <h4 class="font-bold text-lg mb-3">{{ $candidat?->nom_candidat ?? 'Candidat ' . $item['num'] }}</h4>

                                    <dl class="space-y-3">
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Score') }}</dt>
                                            <dd class="text-2xl font-bold {{ $a->matching_score >= 70 ? 'text-green-600' : ($a->matching_score >= 40 ? 'text-orange-600' : 'text-red-600') }}">
                                                {{ $a->matching_score !== null ? $a->matching_score . '%' : 'N/D' }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Recommandation') }}</dt>
                                            <dd>
                                                @if ($a->recommandation)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $recoColors[$a->recommandation->value] ?? 'bg-gray-100' }}">
                                                        {{ $recoLabels[$a->recommandation->value] ?? $a->recommandation->value }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">N/D</span>
                                                @endif
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Expérience') }}</dt>
                                            <dd class="text-gray-900">{{ $a->annees_experience !== null ? $a->annees_experience . ' ans' : 'N/D' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Niveau d\'études') }}</dt>
                                            <dd class="text-gray-900">{{ $a->niveau_etudes ?? 'N/D' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Compétences extraites') }}</dt>
                                            <dd>
                                                @if (! empty($a->competences_extraites))
                                                    <div class="flex flex-wrap gap-1 mt-1">
                                                        @foreach ($a->competences_extraites as $skill)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">{{ $skill }}</span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-gray-400">N/D</span>
                                                @endif
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Points forts') }}</dt>
                                            <dd>
                                                @if (! empty($a->points_forts))
                                                    <ul class="list-disc list-inside text-sm text-gray-900">
                                                        @foreach ($a->points_forts as $point)
                                                            <li>{{ $point }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-gray-400">N/D</span>
                                                @endif
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Lacunes') }}</dt>
                                            <dd>
                                                @if (! empty($a->lacunes))
                                                    <ul class="list-disc list-inside text-sm text-gray-900">
                                                        @foreach ($a->lacunes as $lacune)
                                                            <li>{{ $lacune }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-gray-400">N/D</span>
                                                @endif
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Compétences manquantes') }}</dt>
                                            <dd>
                                                @if (! empty($a->competences_manquantes))
                                                    <div class="flex flex-wrap gap-1 mt-1">
                                                        @foreach ($a->competences_manquantes as $skill)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">{{ $skill }}</span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-gray-400">N/D</span>
                                                @endif
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm font-medium text-gray-500">{{ __('Justification') }}</dt>
                                            <dd class="text-sm text-gray-700">{{ $a->justification ?? 'N/D' }}</dd>
                                        </div>
                                    </dl>

                                    <div class="mt-4">
                                        <a href="{{ route('analyses.show', $a) }}" class="text-indigo-600 hover:text-indigo-900 text-sm hover:underline">
                                            {{ __('Voir le détail') }} →
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if (isset($conclusion))
                            <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                <h4 class="font-semibold text-gray-900 mb-2">{{ __('Conclusion') }}</h4>
                                <p class="text-gray-700">{{ $conclusion }}</p>
                            </div>
                        @endif

                        <div class="mt-6">
                            <a href="{{ route('offres.comparaison', $offre) }}" class="text-indigo-600 hover:text-indigo-900 text-sm hover:underline">
                                ← {{ __('Comparer d\'autres candidats') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
