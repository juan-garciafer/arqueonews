<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $carpeta->nombre }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- INFO DE LA CARPETA --}}
            <div class="bg-white shadow rounded-lg p-6 mb-6">

                <div x-data="{ editing: false }">

                    {{-- VIEW MODE --}}
                    <div x-show="!editing" class="flex justify-between items-start">

                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">
                                {{ $carpeta->nombre }}
                            </h1>

                            <p class="text-sm text-gray-500 mt-1">
                                Creada el {{ $carpeta->created_at->format('d/m/Y') }}
                            </p>
                        </div>

                        <div class="flex items-center space-x-4">

                            <div class="text-gray-400 text-2xl">
                                📁
                            </div>

                            <button type="button"
                                    class="text-yellow-600 hover:underline"
                                    @click="editing = true">
                                Editar
                            </button>

                        </div>

                    </div>

                    {{-- EDIT MODE --}}
                    <div x-show="editing" class="space-y-3">

                        <form method="POST"
                              action="{{ route('carpetas.update', $carpeta->id) }}">

                            @csrf
                            @method('PUT')

                            <input type="text"
                                   name="nombre"
                                   value="{{ $carpeta->nombre }}"
                                   class="w-full border-gray-300 rounded"
                                   x-ref="input"
                                   x-init="$nextTick(() => $refs.input.focus())"
                                   required>

                            <div class="flex justify-between mt-3">

                                <button type="button"
                                        class="text-gray-600"
                                        @click="editing = false">
                                    Cancelar
                                </button>

                                <button type="submit"
                                        class="bg-blue-600 text-white px-3 py-1 rounded">
                                    Guardar
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

                <div class="mt-4 flex space-x-4">

                    <a href="{{ route('carpetas.index') }}"
                       class="text-gray-600 hover:underline">
                        ← Volver
                    </a>

                </div>

            </div>

            {{-- CONTENIDO FUTURO --}}
            <div class="bg-white shadow rounded-lg p-6">

                <h2 class="text-lg font-semibold mb-4">
                    Noticias dentro de esta carpeta
                </h2>

                <p class="text-gray-500">
                    Aquí aparecerán las noticias guardadas en esta carpeta.
                </p>

                <div class="mt-4 text-center text-gray-400">
                    Sin noticias todavía
                </div>

            </div>

        </div>
    </div>
</x-app-layout>