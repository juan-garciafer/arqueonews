@props([
    'noticia' => null,
    'method' => 'POST',
    'action',
])
<div>
    <form action="{{ $action }}" method="POST">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <div class="mb-4">
            <label for="titulo" class="block text-gray-700 font-bold mb-2">Título:</label>
            <input type="text" id="titulo" name="titulo" value="{{ old('titulo', $noticia->titulo ?? '') }}"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('titulo') border-red-500 @enderror">
            @error('titulo')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="descripcion" class="block text-gray-700 font-bold mb-2">Descripción:</label>
            <textarea id="descripcion" name="descripcion"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('descripcion') border-red-500 @enderror">{{ old('descripcion', $noticia->descripcion ?? '') }}</textarea>
            @error('descripcion')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="url_noticia" class="block text-gray-700 font-bold mb-2">URL de la noticia:</label>
            <input type="text" id="url_noticia" name="url_noticia"
                value="{{ old('url_noticia', $noticia->url_noticia ?? '') }}"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('url_noticia') border-red-500 @enderror">
            @error('url_noticia')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="url_imagen" class="block text-gray-700 font-bold mb-2">URL de la imagen:</label>
            <input type="text" id="url_imagen" name="url_imagen"
                value="{{ old('url_imagen', $noticia->url_imagen ?? '') }}"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('url_imagen') border-red-500 @enderror">
            @error('url_imagen')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="pais" class="block text-gray-700 font-bold mb-2">País:</label>
            <input type="text" id="pais" name="pais" value="{{ old('pais', $noticia->pais ?? '') }}"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('pais') border-red-500 @enderror">
            @error('pais')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="categoria" class="block text-gray-700 font-bold mb-2">Categoría:</label>
            <input type="text" id="categoria" name="categoria"
                value="{{ old('categoria', $noticia->categoria ?? '') }}"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('categoria') border-red-500 @enderror">
            @error('categoria')
                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Actualizar</button>

        @if (session('success'))
            <div class="mt-4 rounded-lg border border-green-200 bg-green-50 p-4 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </form>
</div>
