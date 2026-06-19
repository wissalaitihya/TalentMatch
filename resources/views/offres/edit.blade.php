<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Modifier l\'offre') }} : {{ $offre->titre }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('offres.update', $offre) }}">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="titre" :value="__('Titre')" />
                            <x-text-input id="titre" class="block mt-1 w-full" type="text" name="titre" :value="old('titre', $offre->titre)" required autofocus />
                            <x-input-error :messages="$errors->get('titre')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" name="description" rows="6" required>{{ old('description', $offre->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="competences_requises" :value="__('Compétences requises (une par ligne)')" />
                            <textarea id="competences_requises" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" name="competences_requises" rows="4" required>{{ old('competences_requises', implode("\n", $offre->competences_requises ?? [])) }}</textarea>
                            <x-input-error :messages="$errors->get('competences_requises')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="niveau_experience_min" :value="__('Expérience minimum (années)')" />
                            <x-text-input id="niveau_experience_min" class="block mt-1 w-full" type="number" name="niveau_experience_min" :value="old('niveau_experience_min', $offre->niveau_experience_min)" min="0" required />
                            <x-input-error :messages="$errors->get('niveau_experience_min')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('offres.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">{{ __('Annuler') }}</a>
                            <x-primary-button>{{ __('Mettre à jour') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
