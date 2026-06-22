<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $offre->titre }}
            </h2>
            <a href="{{ route('offres.edit', $offre) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Modifier') }}
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <dl class="grid grid-cols-1 gap-4">
                        <div>
                            <dt class="font-medium text-gray-500">{{ __('Description') }}</dt>
                            <dd class="mt-1 text-gray-900">{{ nl2br(e($offre->description)) }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">{{ __('Compétences requises') }}</dt>
                            <dd class="mt-1 text-gray-900">
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($offre->competences_requises as $competence)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ $competence }}
                                        </span>
                                    @endforeach
                                </div>
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">{{ __('Expérience minimum') }}</dt>
                            <dd class="mt-1 text-gray-900">{{ $offre->niveau_experience_min }} ans</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">{{ __('Candidatures') }}</dt>
                            <dd class="mt-1 text-gray-900">{{ $offre->candidats_count }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Soumettre un CV') }}</h3>
                    <form method="POST" action="{{ route('offres.candidats.store', $offre) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label for="nom_candidat" class="block text-sm font-medium text-gray-700">{{ __('Nom du candidat') }}</label>
                            <input id="nom_candidat" name="nom_candidat" type="text" value="{{ old('nom_candidat') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('nom_candidat')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="cv_texte" class="block text-sm font-medium text-gray-700">{{ __('Texte du CV') }}</label>
                            <textarea id="cv_texte" name="cv_texte" rows="8" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('cv_texte') }}</textarea>
                            @error('cv_texte')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Soumettre le CV') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">{{ __('Candidats soumis') }}</h3>
                        @if ($offre->analyses->where('statut_analyse', \App\Enums\StatutAnalyse::Completed)->count() >= 2)
                            <a href="{{ route('offres.comparaison', $offre) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Comparer des candidats') }}
                            </a>
                        @endif
                    </div>
                    @if ($offre->analyses->isEmpty())
                        <p class="text-gray-500">{{ __('Aucun candidat soumis pour cette offre.') }}</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Nom') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Statut') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Score') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Recommandation') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Justification') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($offre->analyses as $analyse)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <a href="{{ route('analyses.show', $analyse) }}" class="text-indigo-600 hover:text-indigo-900 hover:underline">
                                                    {{ $analyse->candidat?->nom_candidat }}
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                                        'processing' => 'bg-blue-100 text-blue-800',
                                                        'completed' => 'bg-green-100 text-green-800',
                                                        'failed' => 'bg-red-100 text-red-800',
                                                    ];
                                                    $statusLabels = [
                                                        'pending' => 'En attente',
                                                        'processing' => 'En cours',
                                                        'completed' => 'Terminé',
                                                        'failed' => 'Échec',
                                                    ];
                                                   $color = $statusColors[$analyse->statut_analyse->value] ?? 'bg-gray-100 text-gray-800';
                                                   $label = $statusLabels[$analyse->statut_analyse->value] ?? $analyse->statut_analyse->value;
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                                    {{ $label }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $analyse->matching_score !== null ? $analyse->matching_score . '%' : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($analyse->recommandation)
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
                                                        $recoColor = $recoColors[$analyse->recommandation->value] ?? 'bg-gray-100 text-gray-800';
                                                        $recoLabel = $recoLabels[$analyse->recommandation->value] ?? $analyse->recommandation->value;
                                                    @endphp
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $recoColor }}">
                                                        {{ $recoLabel }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                                {{ $analyse->justification ? Str::limit($analyse->justification, 80) : '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $analyse->created_at->format('d/m/Y') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
