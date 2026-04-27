<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class BuildCountriesJson extends Command
{
    protected $signature = 'app:build-countries-json';
    protected $description = 'Genera paises.json con países y coordenadas';

    public function handle()
    {
        $this->info('Descargando dataset de países...');

        
        $response = Http::get('https://raw.githubusercontent.com/mledoze/countries/master/countries.json');

        if (!$response->successful()) {
            $this->error('No se pudo descargar el dataset');
            return;
        }

        $data = collect($response->json());

        $countries = $data->map(function ($c) {
            return [
                'cca2' => $c['cca2'] ?? null,
                'latlng' => $c['latlng'] ?? null,
            ];
        })
            ->filter(fn($c) => $c['cca2'] && $c['latlng'])
            ->values();

        Storage::put('paises.json', json_encode($countries, JSON_PRETTY_PRINT));

        $this->info('paises.json generado correctamente');
    }
}
