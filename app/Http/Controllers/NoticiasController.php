<?php

namespace App\Http\Controllers;

use App\Services\SerpApiService;

use Illuminate\Http\Request;

use App\Models\Noticia;

class NoticiasController extends Controller
{
    public function index(SerpApiService $serp)
    {
        $noticias = Noticia::orderBy('fecha_publicacion', 'desc')->paginate(20);

        return view('noticias.index', compact('noticias'));
        // $data = $serp->getGoogleNews('historia');

        // $news = $data['news_results'] ?? [];

        // return view('noticias.index', compact('noticias'));
    }

    // public function index(Request $request)
    // {
    //     $noticias = Noticia::query()
    //         ->when(
    //             $request->categoria,
    //             fn($q) => $q->where('categoria', $request->categoria)
    //         )
    //         ->when(
    //             $request->fecha,
    //             fn($q) => $q->whereDate('fecha_publicacion', $request->fecha)
    //         )
    //         ->orderBy('fecha_publicacion', 'desc')
    //         ->paginate(20);

    //     return view('noticias.index', compact('noticias'));
    // }
}
