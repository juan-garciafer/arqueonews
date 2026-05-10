<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->

    <!-- <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" /> -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jacques+Francois&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased">
    <div class="min-h-screen bg-gray-100 flex">

        {{-- SIDEBAR --}}
        <aside class="w-56 bg-[#587246] text-white border-r border-[#4b613c] h-screen sticky top-0 shrink-0">
            <x-sidebar :filtros="$filtros ?? []" :paises-filtro="$paisesFiltro ?? collect()" />
        </aside>

        {{-- CONTENIDO --}}
        <div class="flex-1 flex flex-col">

            {{-- TOP NAVBAR --}}
            @include('layouts.navigation')

            {{-- HEADER --}}
            @isset($header)
                <header class="bg-[#587246] text-white shadow">
                    <div class="w-full px-4 sm:px-6 lg:px-8 py-4">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            {{-- MAIN --}}
            <main class="flex-1 p-6">
                {{ $slot }}
            </main>

        </div>
    </div>
    @stack('scripts')
    {{-- <script>
    const markers = @json($markers ?? []);
</script> --}}

</body>

</html>
