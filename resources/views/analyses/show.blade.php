<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $analyse->candidat?->nom_candidat ?? 'Candidat' }}
            </h2>
            <a href="{{ route('offres.show', $analyse->offre) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Retour à l\'offre') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @php
                $statusLabels = [
                    'pending' => 'En attente',
                    'processing' => 'En cours',
                    'completed' => 'Terminé',
                    'failed' => 'Échec',
                ];
                $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'processing' => 'bg-blue-100 text-blue-800',
                    'completed' => 'bg-green-100 text-green-800',
                    'failed' => 'bg-red-100 text-red-800',
                ];
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Analyse du candidat') }}</h3>

                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="font-medium text-gray-500">{{ __('Candidat') }}</dt>
                            <dd class="mt-1 text-gray-900">{{ $analyse->candidat?->nom_candidat ?? 'Inconnu' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">{{ __('Offre') }}</dt>
                            <dd class="mt-1 text-gray-900">{{ $analyse->offre->titre }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">{{ __('Statut') }}</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$analyse->statut_analyse] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusLabels[$analyse->statut_analyse] ?? $analyse->statut_analyse }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">{{ __('Score') }}</dt>
                            <dd class="mt-1 text-gray-900">{{ $analyse->matching_score !== null ? $analyse->matching_score . '%' : '-' }}</dd>
                        </div>
                        @if ($analyse->recommandation)
                        <div>
                            <dt class="font-medium text-gray-500">{{ __('Recommandation') }}</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $recoColors[$analyse->recommandation->value] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $recoLabels[$analyse->recommandation->value] ?? $analyse->recommandation->value }}
                                </span>
                            </dd>
                        </div>
                        @endif
                        <div>
                            <dt class="font-medium text-gray-500">{{ __('Expérience') }}</dt>
                            <dd class="mt-1 text-gray-900">{{ $analyse->annees_experience !== null ? $analyse->annees_experience . ' ans' : '-' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">{{ __('Niveau d\'études') }}</dt>
                            <dd class="mt-1 text-gray-900">{{ $analyse->niveau_etudes ?? '-' }}</dd>
                        </div>
                    </dl>

                    @if (! empty($analyse->competences_extraites))
                    <div class="mt-4">
                        <dt class="font-medium text-gray-500">{{ __('Compétences extraites') }}</dt>
                        <dd class="mt-1">
                            <div class="flex flex-wrap gap-2">
                                @foreach ($analyse->competences_extraites as $competence)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">{{ $competence }}</span>
                                @endforeach
                            </div>
                        </dd>
                    </div>
                    @endif

                    @if (! empty($analyse->langues))
                    <div class="mt-4">
                        <dt class="font-medium text-gray-500">{{ __('Langues') }}</dt>
                        <dd class="mt-1">
                            <div class="flex flex-wrap gap-2">
                                @foreach ($analyse->langues as $langue)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800">{{ $langue }}</span>
                                @endforeach
                            </div>
                        </dd>
                    </div>
                    @endif

                    @if (! empty($analyse->points_forts))
                    <div class="mt-4">
                        <dt class="font-medium text-gray-500">{{ __('Points forts') }}</dt>
                        <dd class="mt-1">
                            <ul class="list-disc list-inside text-gray-900">
                                @foreach ($analyse->points_forts as $point)
                                    <li>{{ $point }}</li>
                                @endforeach
                            </ul>
                        </dd>
                    </div>
                    @endif

                    @if (! empty($analyse->lacunes))
                    <div class="mt-4">
                        <dt class="font-medium text-gray-500">{{ __('Lacunes') }}</dt>
                        <dd class="mt-1">
                            <ul class="list-disc list-inside text-gray-900">
                                @foreach ($analyse->lacunes as $lacune)
                                    <li>{{ $lacune }}</li>
                                @endforeach
                            </ul>
                        </dd>
                    </div>
                    @endif

                    @if (! empty($analyse->competences_manquantes))
                    <div class="mt-4">
                        <dt class="font-medium text-gray-500">{{ __('Compétences manquantes') }}</dt>
                        <dd class="mt-1">
                            <div class="flex flex-wrap gap-2">
                                @foreach ($analyse->competences_manquantes as $competence)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">{{ $competence }}</span>
                                @endforeach
                            </div>
                        </dd>
                    </div>
                    @endif

                    @if ($analyse->justification)
                    <div class="mt-4">
                        <dt class="font-medium text-gray-500">{{ __('Justification') }}</dt>
                        <dd class="mt-1 text-gray-900">{{ $analyse->justification }}</dd>
                    </div>
                    @endif

                    @if ($analyse->message_erreur)
                    <div class="mt-4">
                        <dt class="font-medium text-gray-500">{{ __('Message d\'erreur') }}</dt>
                        <dd class="mt-1 text-red-600">{{ $analyse->message_erreur }}</dd>
                    </div>
                    @endif
                </div>
            </div>

            @include('analyses.partials.chat')

        </div>
    </div>
</x-app-layout>
