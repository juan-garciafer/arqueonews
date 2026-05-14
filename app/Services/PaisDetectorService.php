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

        // 1. Países
        $paises = collect(Cache::remember('paises_data', 3600, function () {
            return Pais::all()->map(function (Pais $pais) {
                return [
                    'id' => $pais->id,
                    'nombre' => TextHelper::normalizar($pais->nombre),
                    'codigo_iso' => $pais->codigo_iso,
                ];
            })->toArray();
        }));

        $countryCandidates = [];
        foreach ($paises as $pais) {
            if (!$pais['nombre']) {
                continue;
            }

            if ($this->matchPalabra($texto, $pais['nombre'])) {
                $countryCandidates[] = [
                    'id'     => $pais['id'],
                    'nombre' => $pais['nombre'],
                    'pos'    => $this->firstMatchPosition($texto, $pais['nombre']),
                    'length' => mb_strlen($pais['nombre'], 'UTF-8'),
                ];
            }
        }

        if (!empty($countryCandidates)) {
            usort($countryCandidates, function ($a, $b) {
                // Primero la posición más temprana (menor valor)
                if ($a['pos'] !== $b['pos']) {
                    return $a['pos'] <=> $b['pos'];
                }
                // A igual posición, la coincidencia más larga
                return $b['length'] <=> $a['length'];
            });

            return Pais::find($countryCandidates[0]['id']);
        }

        $paisModels = Pais::all()->keyBy('id');

        // 2. Keywords + aliases
        $keywords = collect(Cache::remember('keywords_data', 3600, function () {
            return Keyword::with('pais', 'aliases')->get()->map(function (Keyword $keyword) {
                return [
                    'nombre' => TextHelper::normalizar($keyword->nombre),
                    'pais_id' => $keyword->pais_id,
                    'aliases' => $keyword->aliases->map(function ($alias) {
                        return TextHelper::normalizar($alias->nombre);
                    })->filter()->values()->all(),
                ];
            })->toArray();
        }))->sortByDesc(fn($keyword) => mb_strlen($keyword['nombre'] ?? ''));

        foreach ($keywords as $keyword) {
            if (!empty($keyword['pais_id']) && !empty($keyword['nombre'])) {
                if ($this->matchPalabra($texto, $keyword['nombre'])) {
                    return $paisModels[$keyword['pais_id']] ?? null;
                }
            }

            $aliases = collect($keyword['aliases'])->sortByDesc(fn($alias) => mb_strlen($alias));
            foreach ($aliases as $aliasNombre) {
                if (!$aliasNombre) {
                    continue;
                }

                if ($this->matchPalabra($texto, $aliasNombre)) {
                    return $paisModels[$keyword['pais_id']] ?? null;
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
        return preg_match('/(?<![[:alnum:]])' . preg_quote($keyword, '/') . '(?![[:alnum:]])/u', $texto) === 1;
    }

    // private function lastMatchPosition(string $texto, string $keyword): int
    // {
    //     return mb_strripos($texto, $keyword, 0, 'UTF-8') ?: 0;
    // }

    private function firstMatchPosition(string $texto, string $keyword): int
    {
        $pos = mb_stripos($texto, $keyword, 0, 'UTF-8');
        return $pos !== false ? $pos : PHP_INT_MAX;
    }
}
