@props(['noticia', 'carpetas' => [], 'class' => ''])

@auth
    <div class="guardar-noticia-container {{ $class }}" x-data="{ mostrarModal: false }">
        <button @click="mostrarModal = true"
            class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition-colors"
            title="Guardar en carpeta">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M5 5a2 2 0 012-2h6a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5v0m6 0v0"></path>
            </svg>
            Guardar
        </button>

        <div x-show="mostrarModal" @click.away="mostrarModal = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
            <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h3 class="text-lg font-semibold">Guardar noticia en carpeta</h3>
                        <p class="text-sm text-gray-600">Selecciona una carpeta para guardar esta noticia.</p>
                    </div>
                    <button @click="mostrarModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                @if (count($carpetas) === 0)
                    <p class="text-gray-600 mb-4">No tienes carpetas. <a href="{{ route('carpetas.create') }}"
                            class="text-blue-600 hover:underline">Crear una carpeta</a></p>
                @else
                    <div class="space-y-3 mb-6">
                        @foreach ($carpetas as $carpeta)
                            <form method="POST" action="{{ route('carpetas.agregar-noticia', $carpeta->id) }}">
                                @csrf
                                <input type="hidden" name="noticia_id" value="{{ $noticia->id }}">
                                <button type="submit"
                                    class="w-full text-left px-4 py-3 rounded-lg hover:bg-gray-100 text-gray-800">
                                    {{ $carpeta->nombre }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-end gap-2">
                    <button @click="mostrarModal = false"
                        class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endauth

@guest
    <div class="inline-flex items-center gap-2 px-3 py-2 bg-gray-300 text-gray-600 rounded-lg text-sm cursor-not-allowed"
        title="Inicia sesión para guardar noticias">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M5 5a2 2 0 012-2h6a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"></path>
        </svg>
        Guardar (inicia sesión)
    </div>
@endguest
