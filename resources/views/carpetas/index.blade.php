<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mis carpetas
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Mensaje de éxito --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Botón crear --}}
            <div class="mb-6 flex justify-end">
                <a href="{{ route('carpetas.create') }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded">
                    + Nueva carpeta
                </a>
            </div>

            {{-- Lista de carpetas --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                @forelse ($carpetas as $carpeta)
                    <div class="bg-white shadow rounded p-4 flex flex-col justify-between">

                        {{-- CONTENIDO --}}
                        <div x-data="{ editing: false }">

                            {{-- VIEW MODE --}}
                            <div x-show="!editing">

                                <a href="{{ route('carpetas.show', $carpeta->id) }}">
                                    <h3 class="text-lg font-semibold text-gray-800">
                                        {{ $carpeta->nombre }}
                                    </h3>
                                </a>

                                <p class="text-sm text-gray-500 mt-1">
                                    Creada: {{ $carpeta->created_at->format('d/m/Y') }}
                                </p>

                                <div class="mt-4 flex justify-between">

                                    <a href="{{ route('carpetas.show', $carpeta->id) }}"
                                        class="text-blue-600">
                                        Ver
                                    </a>

                                    {{-- EDITAR INLINE --}}
                                    <button type="button" class="text-yellow-600"
                                        @click="editing = true">
                                        Editar
                                    </button>

                                    {{-- ELIMINAR --}}
                                    <div x-data="{ open: false }">

                                        <button type="button" class="text-red-600"
                                            @click="open = true">
                                            Eliminar
                                        </button>

                                        {{-- MODAL --}}
                                        <div x-show="open" x-cloak
                                            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">

                                            <div class="bg-white p-6 rounded shadow-md w-96">

                                                <h2 class="text-lg font-semibold mb-4">
                                                    ¿Seguro que quieres eliminar esta carpeta?
                                                </h2>

                                                <p class="text-gray-600 mb-6">
                                                    Esta acción no se puede deshacer.
                                                </p>

                                                <div class="flex justify-end space-x-3">

                                                    {{-- Cancelar --}}
                                                    <button type="button" class="px-4 py-2 bg-gray-200 rounded"
                                                        @click="open = false">
                                                        Cancelar
                                                    </button>

                                                    {{-- FORM DELETE --}}
                                                    <form method="POST"
                                                        action="{{ route('carpetas.destroy', $carpeta->id) }}">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit"
                                                            class="px-4 py-2 bg-red-600 text-white rounded">
                                                            Eliminar
                                                        </button>
                                                    </form>

                                                </div>

                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>

                            {{-- EDIT MODE --}}
                            <div x-show="editing" class="space-y-3">

                                <form method="POST" action="{{ route('carpetas.update', $carpeta->id) }}">

                                    @csrf
                                    @method('PUT')

                                    <input type="text" name="nombre" value="{{ $carpeta->nombre }}"
                                        class="w-full border-gray-300 rounded" required>

                                    <div class="flex justify-between mt-3">

                                        <button type="button" class="text-gray-600" @click="editing = false">
                                            Cancelar
                                        </button>

                                        <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded">
                                            Guardar
                                        </button>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>
                @empty
                    <div class="col-span-3 text-center text-gray-500">
                        No tienes carpetas todavía
                    </div>
                @endforelse

            </div>

        </div>
    </div>
</x-app-layout>
