<?php

namespace App\Services;

use App\Models\Keyword;
use App\Models\Pais;
use Illuminate\Support\Facades\Cache;
use App\Helpers\TextHelper;


class PaisDetectorService
{
    public function detectarPais(string $texto): ?Pais
    {
        $texto = TextHelper::normalizar($texto);

        // 1. Países (cache)
        $paises = Cache::remember('paises', 3600, function () {
            return Pais::all();
        });

        foreach ($paises as $pais) {

            if (!$pais->nombre) continue;

            $nombre = TextHelper::normalizar($pais->nombre);

            if ($this->matchPalabra($texto, $nombre)) {
                return $pais;
            }
        }

        // 2. Keywords + aliases (cache)
        $keywords = Cache::remember('keywords', 3600, function () {
            return Keyword::with('pais', 'aliases')->get();
        });

        foreach ($keywords as $keyword) {

            if ($keyword->pais && $keyword->nombre) {

                $nombreKeyword = TextHelper::normalizar($keyword->nombre);

                if ($this->matchPalabra($texto, $nombreKeyword)) {
                    return $keyword->pais;
                }
            }

            foreach ($keyword->aliases as $alias) {

                if (!$alias->nombre) continue;

                $aliasNombre = TextHelper::normalizar($alias->nombre);

                if ($this->matchPalabra($texto, $aliasNombre)) {
                    return $keyword->pais;
                }
            }
        }

        return null;
    }

    /**
     * Match seguro por palabra completa
     */
    private function matchPalabra(string $texto, string $keyword): bool
    {
        return preg_match('/(^|\s)' . preg_quote($keyword, '/') . '($|\s)/u', $texto);
    }
}
