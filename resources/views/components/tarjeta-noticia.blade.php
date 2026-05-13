@props(['noticia', 'carpetas' => []])

<div class="tarjeta-noticia h-full flex flex-col overflow-visible">
    <!-- Título-->
    <div class="flex-shrink-0">
        <h2 class="text-xl font-semibold text-gray-800 hover:text-blue-600 leading-tight"
            style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
            <a href="{{ route('noticias.visitar', $noticia) }}" target="_blank" rel="noopener nofollow"
                class="hover:underline">
                {{ $noticia->titulo }}
            </a>
        </h2>
    </div>

    <!-- Imagen -->
    <div class="basis-3/5 min-h-0 rounded-lg overflow-hidden my-2 h-48">
        @if ($noticia->url_imagen)
            <img src="{{ $noticia->url_imagen }}" alt="{{ $noticia->titulo }}"
                class="w-full h-full object-cover object-center" onerror="this.style.display='none';">
        @else
            <span class="text-sm text-gray-400">Sin imagen</span>
        @endif
    </div>

    <!-- Descripción + botones -->
    <div class="mt-auto flex flex-col gap-2">
        <p class="text-gray-700 text-lg leading-snug"
            style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
            {{ $noticia->descripcion }}</p>
        @if ($noticia->pais)
            <p class="text-xs text-gray-500">País: {{ $noticia->pais }}</p>
        @else
            <p class="text-xs text-gray-500">País: Sin determinar</p>
        @endif

        <div class="flex items-center gap-2 flex-wrap">
            <x-guardar-noticia :noticia="$noticia" :carpetas="$carpetas" />
            <div class="relative">
                <x-compartir-noticia :noticia="$noticia" />
            </div>
            @auth
                <x-like-button :noticia="$noticia" :liked="$noticia->liked_by_user" :likesCount="$noticia->likes_count" />
            @endauth
            @role('admin|editor')
                <a href="{{ route('noticias.edit', $noticia) }}" class="text-sm text-blue-600 hover:underline">Editar</a>
            @endrole
        </div>
    </div>
</div>
