<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Noticia;
use App\Models\Pais;

class NoticiasController extends Controller
{
    public function index(Request $request)
    {
        $filtros = [
            'orden' => $request->get('orden', 'desc'),
            'pais' => $request->get('pais'),
        ];

        $noticias = Noticia::query();

        // Filtrar por país (usando el campo 'pais')
        if (!empty($filtros['pais'])) {
            $noticias->where('pais', $filtros['pais']);
        }

        // Ordenar por fecha
        $noticias->orderBy('fecha_publicacion', $filtros['orden']);

        $vista = request()->query('vista', 'compacta');
        $noticias = $noticias->paginate($vista === 'compacta' ? 4 : 10)->withQueryString();

        // Obtener países únicos de las noticias (del campo 'pais')
        $paisesFiltro = Noticia::whereNotNull('pais')
            ->where('pais', '!=', '')
            ->distinct()
            ->orderBy('pais')
            ->pluck('pais')
            ->map(fn($pais) => (object) ['nombre' => $pais, 'codigo_iso' => $pais]);

        // Si necesitas marcadores para el mapa
        $markers = collect(); // O implementa según necesites

        $carpetas = auth()->check()
            ? \App\Models\Carpeta::where('user_id', auth()->id())->get()
            : collect();

        return view('noticias.index', [
            'noticias' => $noticias,
            'markers' => $markers,
            'carpetas' => $carpetas,
            'filtros' => $filtros,
            'paisesFiltro' => $paisesFiltro,
        ]);
    }

    public function porPais(Pais $pais)
    {
        $noticias = Noticia::query()
            ->where('codigo_pais', $pais->codigo_iso)
            ->orderBy('fecha_publicacion', 'desc')
            ->paginate(10);

        $carpetas = auth()->check()
            ? \App\Models\Carpeta::where('user_id', auth()->id())->get()
            : collect();

        return view('noticias.pais', [
            'pais' => $pais,
            'noticias' => $noticias,
            'carpetas' => $carpetas,
        ]);
    }

    public function edit(Noticia $noticia)
    {
        return view('noticias.edit', [
            'noticia' => $noticia,
        ]);
    }

    public function update(Request $request, Noticia $noticia)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'url_noticia' => 'required|url|max:255',
            'url_imagen' => 'nullable|url|max:255',
            'pais' => 'nullable|string|max:255',
            'categoria' => 'nullable|string|max:255',
        ]);

        $noticia->update([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'url_noticia' => $request->url_noticia,
            'url_imagen' => $request->url_imagen,
            'pais' => $request->pais,
            'categoria' => $request->categoria,
        ]);

        return redirect()->route('noticias.index')->with('success', 'Noticia actualizada correctamente.');
    }
}
