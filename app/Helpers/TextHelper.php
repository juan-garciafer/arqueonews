<?php

namespace App\Helpers;

class TextHelper
{
    public static function normalizar($texto)
    {
        if (!$texto) {
            return '';
        }

        // 1. forzar string
        $texto = (string) $texto;

        // 2. asegurar UTF-8 válido
        $texto = mb_convert_encoding($texto, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');

        // 3. convertir a minúsculas
        $texto = mb_strtolower($texto, 'UTF-8');

        // 4. quitar caracteres raros invisibles
        $texto = preg_replace('/[^\P{C}\n]+/u', ' ', $texto);

        // 5. transliteración segura (sin romperse)
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);

        return $texto;
    }
}
