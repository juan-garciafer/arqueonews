<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Pais;

class UpdatePaisesCoords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-paises-coords';

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
    $this->info('Cargando dataset local...');

    $path = storage_path('app/private/paises.json');

    if (!file_exists($path)) {
        $this->error('No existe paises.json en storage/app');
        return;
    }

    $paisesApi = collect(json_decode(file_get_contents($path), true))
        ->filter(fn($p) => isset($p['cca2']))
        ->keyBy('cca2');

    $paisesDb = Pais::all();

    foreach ($paisesDb as $pais) {

        $match = $paisesApi[$pais->codigo_iso] ?? null;

        if (!$match || empty($match['latlng'])) {
            continue;
        }

        $pais->update([
            'lat' => $match['latlng'][0],
            'lng' => $match['latlng'][1],
        ]);
    }

    $this->info('Coordenadas actualizadas correctamente desde dataset local');
}
}
