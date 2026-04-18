<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Crear carpeta
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                {{-- Errores de validación --}}
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('carpetas.store') }}" x-data="{ loading: false }"
                    @submit="loading = true">
                    @csrf

                    {{-- Nombre --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">
                            Nombre de la carpeta
                        </label>

                        <input type="text" name="nombre" value="{{ old('nombre') }}"
                            class="w-full border-gray-300 rounded shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 @error('nombre') border-red-500 @enderror"
                            placeholder="Ej: Roma, Grecia, Siglo XV,..." required maxlength="255">

                        @error('nombre')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Botones --}}
                    <div class="flex justify-between items-center">

                        <a href="{{ route('carpetas.index') }}" class="text-gray-600 hover:underline">
                            Cancelar
                        </a>

                        <button type="submit" :disabled="loading"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded disabled:opacity-50">
                            <span x-show="!loading">Crear carpeta</span>
                            <span x-show="loading">Creando...</span>
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>
