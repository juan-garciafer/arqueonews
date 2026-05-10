@props([
    'filtros' => [],
    'paisesFiltro' => collect(),
])

<div class="h-full p-4 overflow-y-auto">

    <h2 class="text-lg font-bold text-white mb-6">
        ArqueoNews
    </h2>

    <nav class="space-y-2">



        {{-- Noticias --}}

         <a href="{{ route('noticias.index') }}" class="block px-3 py-2 rounded text-white hover:bg-[#4b613c]">
            📰 Noticias
        </a>


        {{-- Carpetas --}}
        <a href="{{ route('carpetas.index') }}" class="block px-3 py-2 rounded text-white hover:bg-[#4b613c]">
            📁 Mis carpetas
        </a>

        {{-- Crear carpeta --}}
        <a href="{{ route('carpetas.create') }}" class="block px-3 py-2 rounded text-white hover:bg-[#4b613c]">
            ➕ Nueva carpeta
        </a>        

    </nav>

    @if (request()->routeIs('noticias.index'))
        <hr class="my-4 border-[#4b613c]">

        <div class="rounded-md border border-[#4b613c] bg-[#4b613c]/30 p-3">
            <h3 class="mb-3 text-sm font-semibold text-white">Filtrar noticias</h3>

            <form action="{{ route('noticias.index') }}" method="GET" class="space-y-3">
                <div>
                    <label for="filtro_fecha" class="mb-1 block text-xs text-[#d6e0cf]">Fecha</label>
                    <input
                        id="filtro_fecha"
                        name="fecha"
                        type="date"
                        value="{{ $filtros['fecha'] ?? '' }}"
                        class="w-full rounded border-[#4b613c] bg-white/95 text-sm text-gray-800 focus:border-[#606E8C] focus:ring-[#606E8C]"
                    >
                </div>

                <div>
                    <label for="filtro_pais" class="mb-1 block text-xs text-[#d6e0cf]">Pais</label>
                    <select
                        id="filtro_pais"
                        name="pais"
                        class="w-full rounded border-[#4b613c] bg-white/95 text-sm text-gray-800 focus:border-[#606E8C] focus:ring-[#606E8C]"
                    >
                        <option value="">Todos</option>
                        @foreach ($paisesFiltro as $pais)
                            <option
                                value="{{ strtoupper($pais->codigo_iso) }}"
                                @selected(($filtros['pais'] ?? '') === strtoupper($pais->codigo_iso))
                            >
                                {{ $pais->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 rounded px-3 py-2 text-sm font-semibold text-white">
                        Aplicar
                    </button>
                    <a href="{{ route('noticias.index') }}" class="rounded px-3 py-2 text-sm font-semibold text-white">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>
    @endif

</div>
