<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Alias;
use App\Models\Keyword;

class ImportWikidataAliases extends Command
{
    protected $signature = 'import:wikidata-aliases';
    protected $description = 'Importa aliases desde Wikidata';

    public function handle()
    {
        $this->info("Importando aliases desde Wikidata...");

        $query = '
SELECT ?item ?itemLabel ?country WHERE {
  ?item wdt:P31/wdt:P279* wd:Q33506.
  ?item wdt:P17 ?country.

  SERVICE wikibase:label {
    bd:serviceParam wikibase:language "es,en".
  }
}
LIMIT 1000';

        $response = Http::withHeaders([
            'Accept' => 'application/sparql-results+json',
            'User-Agent' => 'Laravel Wikidata Importer (arqueonews)'
        ])
            ->timeout(60)
            ->asForm()
            ->post('https://query.wikidata.org/sparql', [
                'query' => $query
            ]);

        if (!$response->successful()) {
            $this->error("Error HTTP en Wikidata");
            return;
        }

        $data = $response->json();

        $grouped = [];

        foreach ($data['results']['bindings'] as $item) {

            $wikidataUrl = $item['item']['value'] ?? null;

            if (!$wikidataUrl) continue;

            $wikidataId = basename($wikidataUrl);

            // fallback porque NO hay altLabel en tu query
            $aliasName = $item['itemLabel']['value'] ?? null;

            if (!$aliasName) continue;

            $aliasName = mb_strtolower(trim($aliasName));
            $aliasName = preg_replace('/\s+/', ' ', $aliasName);

            $grouped[$wikidataId][] = $aliasName;
        }

        $keywords = Keyword::all()->keyBy('wikidata_id');

        foreach ($grouped as $wikidataId => $aliases) {

            $keyword = $keywords[$wikidataId] ?? null;

            if (!$keyword) continue;

            foreach (array_unique($aliases) as $aliasName) {

                if (strlen($aliasName) < 3) continue;

                Alias::updateOrCreate(
                    [
                        'keyword_id' => $keyword->id,
                        'nombre' => $aliasName
                    ]
                );

                $this->info("Alias guardado: $aliasName");
            }
        }
        $this->info("Importación completada.");
    }
}
