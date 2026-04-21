<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaisDetectorService;

class PaisDetectorController extends Controller
{
    protected $service;

    public function __construct(PaisDetectorService $service)
    {
        $this->service = $service;
    }

    public function detectar(Request $request)
    {
        $request->validate([
            'texto' => 'required|string|max:5000',
        ]);

        $texto = $request->input('texto', '');

        $pais = $this->service->detectarPais($texto);

        return response()->json([
            'pais' => $pais ? [
                'id' => $pais->id,
                'nombre' => $pais->nombre,
                'codigo' => $pais->codigo_iso ?? null,
            ] : null,
            'detected' => (bool) $pais,
        ]);
    }
}
