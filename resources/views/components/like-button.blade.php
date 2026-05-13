@props(['noticia', 'liked' => false, 'likesCount' => 0])

<div x-data="{
    liked: {{ $liked ? 'true' : 'false' }},
    likesCount: {{ $likesCount }},
    noticiaId: {{ $noticia->id }},

    toggleLike() {
        fetch(`/noticias/${this.noticiaId}/like`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') ?? '',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw new Error(err.message || 'Error del servidor');
                    });
                }
                return response.json();
            })
            .then(data => {
                this.liked = data.liked;
                this.likesCount = data.likes_count;
            })
            .catch(error => {
                console.error('Error al dar like:', error.message);
                alert('No se pudo procesar tu like. Intenta recargar la página.');
            });
    }
}" class="inline-flex items-center gap-1">

    <button @click="toggleLike()" class="focus:outline-none transition-transform hover:scale-110 duration-200"
        :class="{ 'text-red-500': liked, 'text-gray-400': !liked }" :title="liked ? 'Quitar me gusta' : 'Dar me gusta'">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 fill-current" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
    </button>

    <span class="text-sm font-medium text-gray-600" x-text="likesCount"></span>
</div>
