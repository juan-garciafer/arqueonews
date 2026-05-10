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
        $this->info("Importando aliases desde Wikidata...\n");

        $offset = 0;
        $limit = 100;
        $hasMore = true;
        $totalProcessed = 0;

        while ($hasMore) {
            $query = "
SELECT ?item ?itemLabel ?aliasLabel ?country WHERE {
  VALUES ?type { wd:Q33506 wd:Q515 wd:Q41176 }
  ?item wdt:P31 ?type.
  ?item wdt:P17 ?country.
  OPTIONAL { ?item skos:altLabel ?aliasLabel. FILTER(LANG(?aliasLabel) IN (\"es\", \"en\")) }

  SERVICE wikibase:label {
    bd:serviceParam wikibase:language \"es,en\".
  }
}
LIMIT $limit OFFSET $offset";

            $this->line("📍 Procesando offset: $offset...");

            $response = null;
            $attempts = 0;
            $delay = 1;

            while ($attempts < 4) {
                $attempts++;

                $response = Http::withHeaders([
                    'Accept' => 'application/sparql-results+json',
                    'User-Agent' => 'Laravel Wikidata Importer (arqueonews)'
                ])
                    ->timeout(120)
                    ->asForm()
                    ->post('https://query.wikidata.org/sparql', [
                        'query' => $query
                    ]);

                if ($response->successful()) {
                    break;
                }

                $status = $response->status();
                $this->warn("   ⚠ Petición fallida (HTTP $status) en offset $offset, intento $attempts/4");

                if ($status >= 500 && $status < 600 && $attempts < 4) {
                    sleep($delay);
                    $delay *= 2;
                    continue;
                }

                break;
            }

            if (!$response || !$response->successful()) {
                $status = $response ? $response->status() : 'desconocido';
                $this->warn("   ❌ No se pudo completar la petición en offset $offset (HTTP $status)");

                if ($limit > 25) {
                    $limit = max(25, (int) floor($limit / 2));
                    $this->warn("   ⚠ Reducción de lote a $limit y reintento en mismo offset");
                    sleep(2);
                    continue;
                }

                break;
            }

            $data = $response->json();
            $bindings = $data['results']['bindings'] ?? [];

            if (empty($bindings)) {
                $hasMore = false;
                break;
            }

            $grouped = [];

            foreach ($bindings as $item) {
                $wikidataUrl = $item['item']['value'] ?? null;

                if (!$wikidataUrl) continue;

                $wikidataId = basename($wikidataUrl);
                $aliasName = $item['aliasLabel']['value'] ?? null;

                if (!$aliasName) continue;

                $aliasName = mb_strtolower(trim($aliasName));
                $aliasName = preg_replace('/\s+/', ' ', $aliasName);

                $grouped[$wikidataId][] = $aliasName;
            }

            $keywords = Keyword::whereIn('wikidata_id', array_keys($grouped))->get()->keyBy('wikidata_id');

            $count = 0;
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

                    $count++;
                    $totalProcessed++;
                }
            }

            $this->line("   ✅ Guardados: $count aliases");

            if (count($bindings) < $limit) {
                $hasMore = false;
            } else {
                $offset += $limit;
                sleep(1); // Esperar 1 segundo entre peticiones
            }
        }

        $this->info("\n✅ Importación completada. Total: $totalProcessed aliases.");
    }
}
