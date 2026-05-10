<?php

namespace App\Console\Commands;

use App\Models\Noticia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Helpers\TextHelper;

class CleanupNoticiasDuplicadas extends Command
{
    protected $signature = 'cleanup:noticias-duplicadas';
    protected $description = 'Elimina noticias duplicadas en la base de datos usando título y descripción.';

    public function handle()
    {
        $this->info('Buscando noticias duplicadas...');

        $deleted = 0;
        $updated = 0;

        // 1. Primero detectar y eliminar duplicados por contenido (sin considerar hash)
        $this->info('Eliminando duplicados por contenido...');

        $noticias = Noticia::orderBy('id', 'asc')->get();
        $seenContent = [];

        foreach ($noticias as $noticia) {
            $texto = trim(($noticia->titulo ?? '') . ' ' . ($noticia->descripcion ?? ''));
            if ($texto === '') {
                continue;
            }

            $normalized = TextHelper::normalizar($texto);

            if (isset($seenContent[$normalized])) {
                $this->line("   Eliminado duplicado: {$noticia->titulo}");
                $noticia->delete();
                $deleted++;
            } else {
                $seenContent[$normalized] = $noticia->id;
            }
        }

        $this->info("Duplicados eliminados: $deleted");

        // 2. Luego asignar hashes a noticias sin hash
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
