<div class="h-full p-4">

    <h2 class="text-lg font-bold text-gray-800 mb-6">
        ArqueoNews
    </h2>

    <nav class="space-y-2">

        {{-- Inicio --}}
        <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-100">
            🏠 Inicio
        </a>

        {{-- Noticias --}}

         <a href="{{ route('noticias.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100">
            📰 Noticias
        </a>


        {{-- Carpetas --}}
        <a href="{{ route('carpetas.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100">
            📁 Mis carpetas
        </a>

        {{-- Crear carpeta --}}
        <a href="{{ route('carpetas.create') }}" class="block px-3 py-2 rounded hover:bg-gray-100">
            ➕ Nueva carpeta
        </a>

        {{-- Espacio para futuras opciones --}}
        <hr class="my-3">

        <a href="#" class="block px-3 py-2 rounded text-gray-400">
            ⚙️ Configuración (próximamente)
        </a>

    </nav>

</div>
