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
    protected $signature = 'app:reprocesar-pais';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (Noticia::whereNull('pais')->get() as $noticia) {
            $pais = $this->paisDetector->detectarPais($noticia->titulo . ' ' . $noticia->descripcion);

            if ($pais) {
                $noticia->pais = $pais->id;
                $noticia->save();
                $this->info("Noticia ID {$noticia->id} actualizada con país: {$pais->nombre}");
            }
        }
    }
}
