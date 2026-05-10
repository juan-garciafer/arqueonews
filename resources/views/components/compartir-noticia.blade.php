@props(['noticia', 'class' => ''])

<div class="compartir-noticia-container relative{{ $class }}" x-data="{ mostrarOpciones: false }">
    <!-- Botón para compartir -->
    <button 
        @click="mostrarOpciones = !mostrarOpciones"
        class="inline-flex items-center gap-2 px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm transition-colors"
        title="Compartir noticia"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C9.766 12.938 10 12.649 10 12c0-.649-.234-.938-1.316-1.342m0 2.684a3 3 0 110-2.684m9.108-2.684c-.576.094-.912.234-.912.90a.39.39 0 00.034.147m0-.147c0-.649.234-.938 1.316-1.342m0 2.684a3 3 0 11-6-2.684m0 2.684a3 3 0 110-2.684"></path>
        </svg>
        Compartir
    </button>

    <!-- Opciones de compartir -->
    <div x-show="mostrarOpciones" x-transition @click.outside="mostrarOpciones = false" class="absolute mt-2 bg-white rounded-lg shadow-lg p-2 min-w-48 z-40">
        
        <!-- Copiar URL -->
        <button 
            @click="copiarAlPortapapeles('{{ $noticia->url_noticia }}')"
            class="w-full text-left px-4 py-2 hover:bg-gray-100 rounded-lg transition-colors flex items-center gap-2 text-gray-700"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
            Copiar URL
        </button>

        <!-- Twitter/X -->
        <a 
            href="https://twitter.com/intent/tweet?url={{ urlencode($noticia->url_noticia) }}&text={{ urlencode($noticia->titulo) }}"
            target="_blank"
            class="w-full text-left px-4 py-2 hover:bg-gray-100 rounded-lg transition-colors flex items-center gap-2 text-gray-700"
        >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2s9 5 20 5a9.5 9.5 0 00-9-5.5c4.75 2.25 10.5.5 10.5.5"></path>
            </svg>
            Compartir en X
        </a>

        <!-- Facebook -->
        <a 
            href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($noticia->url_noticia) }}"
            target="_blank"
            class="w-full text-left px-4 py-2 hover:bg-gray-100 rounded-lg transition-colors flex items-center gap-2 text-gray-700"
        >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18 2h-3a6 6 0 00-6 6v3H7v4h2v8h4v-8h3l1-4h-4V8a1 1 0 011-1h3z"></path>
            </svg>
            Compartir en Facebook
        </a>

        <!-- LinkedIn -->
        <a 
            href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($noticia->url_noticia) }}"
            target="_blank"
            class="w-full text-left px-4 py-2 hover:bg-gray-100 rounded-lg transition-colors flex items-center gap-2 text-gray-700"
        >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"></path>
                <circle cx="4" cy="4" r="2"></circle>
            </svg>
            Compartir en LinkedIn
        </a>

        <!-- WhatsApp -->
        <a 
            href="https://wa.me/?text={{ urlencode($noticia->titulo . ' ' . $noticia->url_noticia) }}"
            target="_blank"
            class="w-full text-left px-4 py-2 hover:bg-gray-100 rounded-lg transition-colors flex items-center gap-2 text-gray-700"
        >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.67-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421-7.403h-.004a9.87 9.87 0 00-5.031 1.378c-3.055 2.316-3.83 6.614-.857 9.96 3.173 3.8 8.823 4.518 12.884 1.12l4.6 1.202-1.238-4.57c2.47-3.432 2.389-8.294-.798-11.67-3.191-3.392-8.459-3.755-12.055-.42"></path>
            </svg>
            Compartir en WhatsApp
        </a>

        <!-- Email -->
        <a 
            href="mailto:?subject={{ urlencode($noticia->titulo) }}&body={{ urlencode($noticia->descripcion . '\n\n' . $noticia->url_noticia) }}"
            class="w-full text-left px-4 py-2 hover:bg-gray-100 rounded-lg transition-colors flex items-center gap-2 text-gray-700"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            Compartir por correo
        </a>

    </div>
</div>

<script>

    function mostrarToast(mensaje, tipo = 'ok') {
    const toast = document.createElement('div');
    toast.textContent = mensaje;

    toast.style.position = 'fixed';
    toast.style.bottom = '20px';
    toast.style.right = '20px';
    toast.style.padding = '10px 15px';
    toast.style.borderRadius = '8px';
    toast.style.color = 'white';
    toast.style.zIndex = '9999';
    toast.style.backgroundColor = tipo === 'ok' ? '#22c55e' : '#ef4444';

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 2500);
}

    function copiarAlPortapapeles(texto) {
        navigator.clipboard.writeText(texto).then(() => {
            mostrarToast('URL copiada al portapapeles', 'ok');
        }).catch(err => {
            console.error('Error al copiar:', err);
            mostrarToast('Error al copiar la URL', 'error');
        });
    }
</script>
