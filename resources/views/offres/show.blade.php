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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
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
        </div>
    </div>
</x-app-layout>
