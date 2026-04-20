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
