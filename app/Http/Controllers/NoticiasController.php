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
            'fecha' => $request->query('fecha'),
            'pais' => strtoupper((string) $request->query('pais', '')),
        ];

        $baseQuery = Noticia::query();

        if (!empty($filtros['fecha'])) {
            $baseQuery->whereDate('fecha_publicacion', $filtros['fecha']);
        }

        if (!empty($filtros['pais'])) {
            $baseQuery->whereRaw('UPPER(codigo_pais) = ?', [$filtros['pais']]);
        }


        $vista = request()->query('vista', 'compacta');

        $noticias = (clone $baseQuery)
            ->orderBy('fecha_publicacion', 'desc')
            ->paginate($vista === 'compacta' ? 4 : 10)
            ->withQueryString();

        $paises = Pais::all()->keyBy(fn($p) => strtoupper($p->codigo_iso));

        $markers = (clone $baseQuery)
            ->get()
            ->filter(fn($n) => $n->codigo_pais)
            ->groupBy(fn($n) => strtoupper($n->codigo_pais))
            ->map(function ($group, $codigo) use ($paises) {

                $pais = $paises[$codigo] ?? null;

                if (!$pais || !$pais->lat || !$pais->lng) {
                    return null;
                }

                return [
                    'id' => $pais->id,
                    'lat' => $pais->lat,
                    'lng' => $pais->lng,
                    'nombre' => $pais->nombre,
                    'count' => $group->count(),

                    'noticias' => $group->map(fn($n) => [
                        'id' => $n->id,
                        'titulo' => $n->titulo,
                        'descripcion' => $n->descripcion,
                        'url' => $n->url_noticia,
                        'imagen' => $n->url_imagen,
                    ])->values()->toArray(),
                ];
            })
            ->filter()
            ->values();

        // dd($markers);

        $carpetas = auth()->check()
            ? \App\Models\Carpeta::where('user_id', auth()->id())->get()
            : collect();

        return view('noticias.index', [
            'noticias' => $noticias,
            'markers' => $markers ?? collect(),
            'carpetas' => $carpetas,
            'filtros' => $filtros,
            'paisesFiltro' => $paises->sortBy('nombre')->values(),
        ]);
    }

    public function porPais(Pais $pais)
    {
        $noticias = Noticia::query()
            ->whereRaw('UPPER(codigo_pais) = ?', [strtoupper((string) $pais->codigo_iso)])
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
}
