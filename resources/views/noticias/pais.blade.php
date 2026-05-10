<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Noticias de {{ $pais->nombre }}
        </h2>
    </x-slot>

    <div class="w-5/6 max-w-7xl mx-auto">
        <div class="mb-4">
            <a href="{{ route('noticias.index') }}" class="text-sm text-blue-600 hover:underline">
                ← Volver a noticias
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @forelse ($noticias as $noticia)
            <div class="mb-4">
                <x-tarjeta-noticia :noticia="$noticia" :carpetas="$carpetas" />
            </div>
        @empty
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                <p class="text-gray-700">No hay noticias disponibles para este país.</p>
            </div>
        @endforelse

        <div class="mt-6">
            {{ $noticias->links() }}
        </div>
    </div>
</x-app-layout>
