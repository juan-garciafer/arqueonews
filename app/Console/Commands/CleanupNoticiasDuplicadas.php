<?php

namespace App\Console\Commands;

use App\Models\Noticia;
use Illuminate\Console\Command;
use App\Helpers\TextHelper;

class CleanupNoticiasDuplicadas extends Command
{
    protected $signature = 'cleanup:noticias-duplicadas';
    protected $description = 'Elimina noticias duplicadas en la base de datos usando título y descripción, o solo título.';

    public function handle()
    {
        $this->info('Buscando noticias duplicadas...');

        $deleted = 0;
        $updated = 0;

        // 1. Detectar y eliminar duplicados por contenido y por título
        $this->info('Eliminando duplicados por contenido o por título...');

        $noticias = Noticia::orderBy('id', 'asc')->get();
        $seenContent = [];
        $seenTitles  = [];

        foreach ($noticias as $noticia) {
            $titulo      = trim($noticia->titulo ?? '');
            $descripcion = trim($noticia->descripcion ?? '');

            // Texto completo (título + descripción)
            $fullText = $titulo . ' ' . $descripcion;
            if ($fullText === '') {
                continue;
            }

            $normalizedFull = TextHelper::normalizar($fullText);

            // Texto del título solo (si no está vacío)
            $normalizedTitle = null;
            if ($titulo !== '') {
                $normalizedTitle = TextHelper::normalizar($titulo);
            }

            // ¿Está duplicado por contenido completo o por título?
            $isDuplicate = false;

            if (isset($seenContent[$normalizedFull])) {
                $isDuplicate = true;
            } elseif ($normalizedTitle !== null && isset($seenTitles[$normalizedTitle])) {
                $isDuplicate = true;
            }

            if ($isDuplicate) {
                $this->line("   Eliminado duplicado: {$noticia->titulo}");
                $noticia->delete();
                $deleted++;
            } else {
                // Registrar la noticia como vista
                $seenContent[$normalizedFull] = true;
                if ($normalizedTitle !== null) {
                    $seenTitles[$normalizedTitle] = true;
                }
            }
        }

        $this->info("Duplicados eliminados: $deleted");

        // 2. Asignar hashes a noticias sin hash
        $this->info('Asignando hashes a noticias sin hash...');
        Noticia::whereNull('hash')
            ->chunk(200, function ($noticias) use (&$updated) {
                foreach ($noticias as $noticia) {
                    $texto = trim(($noticia->titulo ?? '') . ' ' . ($noticia->descripcion ?? ''));
                    if ($texto === '') {
                        continue;
                    }

                    $hash = md5(TextHelper::normalizar($texto));

                    // Verificar si el hash ya existe para evitar conflict
                    if (!Noticia::where('hash', $hash)->where('id', '!=', $noticia->id)->exists()) {
                        $noticia->forceFill(['hash' => $hash])->saveQuietly();
                        $updated++;
                    }
                }
            });

        $this->info("Hashes asignados: $updated");
        $this->info("\nProceso completado.");
    }
}