<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar noticia
        </h2>

    <x-formulario-noticia :noticia="$noticia" method="PUT" :action="route('noticias.update', $noticia)" />
</x-app-layout>
