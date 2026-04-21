<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Keyword;

class ImportWikidataKeywords extends Command
{
    protected $signature = 'import:wikidata-keywords';
    protected $description = 'Importa palabras clave desde Wikidata';

    public function handle()
    {
        $this->info("Importando palabras clave desde Wikidata...");

        $query = '
        SELECT ?item ?itemLabel ?countryLabel WHERE {
  ?item wdt:P31/wdt:P279* wd:Q33506.  # archaeological site
  ?item wdt:P17 ?country.

  SERVICE wikibase:label {
    bd:serviceParam wikibase:language "es,en".
  }
}
LIMIT 300';

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

            $nombre = $item['itemLabel']['value'] ?? null;
            if (!$nombre) continue;

            $nombre = trim($nombre);

            $wikidataId = basename($item['item']['value']);

            if (!$wikidataId) continue;

            $countryName = $item['countryLabel']['value'] ?? null;
            $pais = \App\Models\Pais::where('nombre', $countryName)->first();

            Keyword::updateOrCreate(
                [
                    'wikidata_id' => $wikidataId
                ],
                [
                    'nombre' => $nombre,
                    'pais_id' => $pais?->id,
                    'tipo' => 'arqueologico'
                ]
            );

            $this->info("Guardado: $nombre");
        }

        $this->info("Importación completada.");
    }
}
