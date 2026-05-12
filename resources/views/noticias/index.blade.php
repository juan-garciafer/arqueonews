<x-app-layout>
    {{-- {{ dd('ESTOY EN LA VISTA CORRECTA', $markers ?? null) }} --}}
    <x-slot name="header">
        {{-- <h2 class="font-semibold text-xl text-gray-800 leading-tight mx-auto">
            {{ __('Noticias') }}
        </h2> --}}
        <x-barra-busqueda />
    </x-slot>
    
    @php
        $vista = request()->query('vista', 'compacta');
        $urlCompacta = request()->fullUrlWithQuery(['vista' => 'compacta', 'page' => 1]);
        $urlLista = request()->fullUrlWithQuery(['vista' => 'lista', 'page' => 1]);
    @endphp

    <div class="mb-4 flex justify-end">
        <div class="inline-flex rounded-lg border border-gray-300 overflow-hidden">
            <a href="{{ $urlCompacta }}" class="px-4 py-2 text-sm {{ $vista === 'compacta' ? 'bg-[#587246] text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                Vista compacta
            </a>
            <a href="{{ $urlLista }}" class="px-4 py-2 text-sm {{ $vista === 'lista' ? 'bg-[#587246] text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                Vista lista
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($vista === 'lista')
        <div class="mb-6 w-5/6 mx-auto">
            <x-mapa-leaflet :markers="$markers" />
        </div>

        @forelse ($noticias as $noticia)
            <div class="mb-4 w-5/6 mx-auto">
                <x-tarjeta-noticia :noticia="$noticia" :carpetas="$carpetas" />
            </div>
        @empty
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                <p class="text-gray-700">No hay noticias disponibles por el momento.</p>
            </div>
        @endforelse

        <div class="mt-6">
            {{ $noticias->appends(request()->query())->links() }}
        </div>
        @else
        <div class="min-h-[calc(100vh-14rem)]">
            <div class="flex flex-col lg:flex-row gap-6">
    
                <div class="w-full lg:w-2/3 grid grid-cols-1 md:grid-cols-2 gap-4 auto-rows-fr">
                    @forelse ($noticias as $noticia)
                        <x-tarjeta-noticia :noticia="$noticia" :carpetas="$carpetas" />
                    @empty
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                            <p class="text-gray-700">No hay noticias disponibles por el momento.</p>
                        </div>
                    @endforelse
                </div>
    
                <div class="w-full lg:w-1/3">
                    <div class="sticky top-4">
                        <x-mapa-leaflet :markers="$markers" />
                    </div>
                </div>
    
            </div>
    
            <div class="mt-6">
                {{ $noticias->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
</x-app-layout>

