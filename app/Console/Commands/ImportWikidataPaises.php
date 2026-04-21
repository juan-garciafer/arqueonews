<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Pais;

class ImportWikidataPaises extends Command
{
    protected $signature = 'import:wikidata-paises';
    protected $description = 'Importa países desde Wikidata';

    public function handle()
    {
        $this->info("Importando países desde Wikidata...");

        $query = '
        SELECT ?country ?countryLabel ?iso WHERE {
  ?country wdt:P31 wd:Q6256.
  OPTIONAL { ?country wdt:P297 ?iso. }

  SERVICE wikibase:label {
    bd:serviceParam wikibase:language "es,en".
  }
}';

        $response = Http::withHeaders([
            'Accept' => 'application/sparql-results+json',
            'User-Agent' => 'Laravel Wikidata Importer (arqueonews)'
        ])
            ->timeout(60)
            ->retry(3, 1000)
            ->asForm()
            ->post('https://query.wikidata.org/sparql', [
                'query' => $query
            ]);

        if (!$response->successful()) {
            $this->error("Error HTTP en Wikidata");
            return;
        }

        $data = $response->json();

        if (!isset($data['results']['bindings'])) {
            $this->error("Respuesta inválida de Wikidata");
            return;
        }

        foreach ($data['results']['bindings'] as $item) {

            $nombre = $item['countryLabel']['value'] ?? null;
            $codigoIso = $item['iso']['value'] ?? null;

            if (!$nombre) continue;

            $nombre = trim($nombre);

            Pais::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'codigo_iso' => $codigoIso,
                ]
            );

            $this->info("Guardado: $nombre");
        }

        $this->info("Importación completada");
    }
}
