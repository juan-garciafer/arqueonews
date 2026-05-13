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

        $paises = Pais::all()->keyBy(fn($p) => strtoupper($p->codigo_iso));

        // Filtrar por país (usando el campo 'pais')
        if (!empty($filtros['pais'])) {
            $noticias->where('pais', $filtros['pais']);
        }

        // Ordenar por fecha
        $noticias->orderBy('fecha_publicacion', $filtros['orden']);

        $noticias->withCount('likes')
            ->withExists(['likes as liked_by_user' => function ($query) {
                $query->where('user_id', auth()->id());
            }]);

        $vista = request()->query('vista', 'compacta');
        $noticias = $noticias->paginate($vista === 'compacta' ? 4 : 10)->withQueryString();

        // Obtener países únicos de las noticias (del campo 'pais')
        $paisesFiltro = Noticia::whereNotNull('pais')
            ->where('pais', '!=', '')
            ->distinct()
            ->orderBy('pais')
            ->pluck('pais')
            ->map(fn($pais) => (object) ['nombre' => $pais, 'codigo_iso' => $pais]);

        $markers = Noticia::all()
            ->filter(fn($n) => $n->codigo_pais)
            ->groupBy(fn($n) => strtoupper($n->codigo_pais))
            ->map(function ($group, $codigo) use ($paises) {

                $pais = $paises[$codigo] ?? null;

                if (!$pais || $pais->lat === null || $pais->lng === null) {
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
            ->withCount('likes')
            ->withExists(['likes as liked_by_user' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
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

    public function visitar(Noticia $noticia, Request $request)
    {
        //Datos del usuario visitante
        $userId = auth()->check() ? auth()->id() : null;
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        //Token para usuarios no registrados (hash de IP + User Agent)
        $tokenInvitado = $userId ? null : md5($ip . $userAgent);

        //Registrar la visita
        $noticia->visitas()->create([
            'user_id' => $userId,
            'token_invitado' => $tokenInvitado,
            'direccion_ip' => $ip,
        ]);

        return redirect()->away($noticia->url_noticia);
    }

    public function toggleLike(Noticia $noticia)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['error' => 'Debes iniciar sesión.'], 401);
            }

            $existing = $user->noticiasLikeadas()->where('noticia_id', $noticia->id)->first();

            if ($existing) {
                $existing->delete();
                $liked = false;
            } else {
                $user->noticiasLikeadas()->attach($noticia->id);
                $liked = true;
            }

            $likesCount = $noticia->likes()->count();

            return response()->json([
                'liked' => $liked,
                'likes_count' => $likesCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al dar like: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor.'], 500);
        }
    }
}
