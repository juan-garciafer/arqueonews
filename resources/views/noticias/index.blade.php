<x-app-layout>
    

    {{-- {{ dd('ESTOY EN LA VISTA CORRECTA', $markers ?? null) }} --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">

        <x-mapa-leaflet :markers="$markers" />

        <h1>Noticias</h1>

        @foreach ($noticias as $noticia)
            <div style="margin-bottom: 20px;">

                <h2>
                    <a href="{{ $noticia->url_noticia }}" target="_blank">
                        {{ $noticia->titulo }}
                    </a>
                </h2>

                @if ($noticia->url_imagen)
                    <img src="{{ $noticia->url_imagen }}" width="200">
                @endif

                <p>{{ $noticia->descripcion }}</p>

                <small>
                    {{ $noticia->categoria }} · {{ $noticia->fecha_publicacion }}
                </small>

            </div>
        @endforeach

        {{ $noticias->links() }}

    </div>
    </div>
</x-app-layout>
