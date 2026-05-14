<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Noticia;
use App\Services\PaisDetectorService;

class ReprocesarPais extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reprocesar-pais {--all : Reprocesar todas las noticias, no solo las que no tienen país.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reprocesa el país de noticias guardadas usando título y descripción';

    protected PaisDetectorService $paisDetector;

    public function __construct(PaisDetectorService $paisDetector)
    {
        parent::__construct();

        $this->paisDetector = $paisDetector;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = $this->option('all') ? Noticia::query() : Noticia::whereNull('pais');
        $updated = 0;
        $processed = 0;

        $query->chunkById(100, function ($noticias) use (&$updated, &$processed) {
            foreach ($noticias as $noticia) {
                $texto = trim(($noticia->titulo ?? '') . ' ' . ($noticia->descripcion ?? ''));
                if ($texto === '') {
                    continue;
                }

                try {
                    $pais = $this->paisDetector->detectarPais($texto);
                } catch (\Throwable $e) {
                    $this->error("Error en noticia {$noticia->id}: " . $e->getMessage());
                    continue;
                }

                // ✅ Verificar si se detectó país
                if (!$pais) {
                    continue;
                }

                $processed++;

                $needsUpdate = $this->option('all')
                    ? ($noticia->pais !== $pais->nombre || $noticia->codigo_pais !== $pais->codigo_iso)
                    : true;

                if ($needsUpdate) {
                    $noticia->pais = $pais->nombre;
                    $noticia->codigo_pais = $pais->codigo_iso;
                    $noticia->save();
                    $updated++;
                    $this->info("Noticia ID {$noticia->id} actualizada con país: {$pais->nombre} ({$pais->codigo_iso})");
                }
            }
        });

        $this->info("\nProceso completado. Noticias procesadas: $processed. Noticias actualizadas: $updated.");
    }
}
