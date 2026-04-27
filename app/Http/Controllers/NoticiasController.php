<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Noticia;
use App\Models\Pais;

class NoticiasController extends Controller
{
    public function index()
    {
        // dd('ESTOY EN NOTICIAS CONTROLLER');
        $noticias = Noticia::orderBy('fecha_publicacion', 'desc')->paginate(20);

        $paises = Pais::all()->keyBy(fn($p) => strtoupper($p->codigo_iso));

        $markers = Noticia::all()
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

        return view('noticias.index', [
            'noticias' => $noticias,
            'markers' => $markers ?? collect()
        ]);
    }
}
