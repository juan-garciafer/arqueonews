<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Keyword;

class ImportWikidataKeywords extends Command
{
    protected $signature = 'import:wikidata-keywords {--skip-cleanup : Omitir eliminación de keywords inútiles}';
    protected $description = 'Importa palabras clave desde Wikidata en lotes, filtrando tipos inútiles';

    // IDs de Wikidata que NO queremos (barcos, vehículos, etc.)
    protected array $excludeTypes = [
        'wd:Q11446',      // Barco de guerra (HMCS, etc.)
        'wd:Q3024240',    // Modelo de vehículo
        'wd:Q2096',       // Clase de nave
        'wd:Q39614',      // Clase de barco militar
        'wd:Q1353099',    // Demonym (gentilicio)
        'wd:Q16521',      // Taxón (biologico)
        'wd:Q15632617',   // Artifact (objeto, no lugar)
    ];

    // Palabras clave que indican que NO es un lugar
    protected array $excludeKeywords = [
        'clase',
        'modelo',
        'tipo',
        'familia',
        'orden',
        'género',
        'especie',
        'variedad',
        'barco',
        'buque',
        'nave',
        'ship',
        'vessel',
        'aircraft',
        'avión',
        'helicóptero',
    ];

    public function handle()
    {
        $this->info("Importando palabras clave desde Wikidata...\n");

        $types = [
            'arqueologico' => 'wd:Q33506',
            'ciudad' => 'wd:Q515',
            'pueblo' => 'wd:Q41176',
        ];

        $totalProcessed = 0;

        foreach ($types as $tipo => $wikidataType) {
            $this->info("📍 Importando $tipo...");

            $offset = 0;
            $limit = 100;
            $hasMore = true;

            while ($hasMore) {
                // Construir filtros SPARQL para excluir tipos inútiles y filtrar por lugares
                $excludeFilter = $this->buildExcludeFilter();
                $locationFilter = $this->buildLocationFilter($tipo);

                $query = "
                SELECT ?item ?itemLabel ?countryLabel WHERE {
  ?item wdt:P31 $wikidataType.
  ?item wdt:P17 ?country.
  
  $excludeFilter
  $locationFilter

  SERVICE wikibase:label {
    bd:serviceParam wikibase:language \"es,en\".
  }
}
LIMIT $limit OFFSET $offset";

                $this->line("   Offset: $offset...");

                $response = Http::withHeaders([
                    'Accept' => 'application/sparql-results+json',
                    'User-Agent' => 'Laravel Wikidata Importer (arqueonews)'
                ])
                    ->timeout(120)
                    ->retry(2, 1000)
                    ->asForm()
                    ->post('https://query.wikidata.org/sparql', [
                        'query' => $query
                    ]);

                if (!$response->successful()) {
                    $this->error("   ❌ Error HTTP en Wikidata (offset $offset)");
                    break;
                }

                $data = $response->json();
                $bindings = $data['results']['bindings'] ?? [];

                if (empty($bindings)) {
                    $hasMore = false;
                    break;
                }

                $count = 0;
                foreach ($bindings as $item) {
                    $nombre = $item['itemLabel']['value'] ?? null;
                    if (!$nombre) continue;

                    $nombre = trim($nombre);
                    $wikidataId = basename($item['item']['value']);

                    if (!$wikidataId) continue;

                    // Filtro adicional: detectar nombres que parecen barcos, vehículos, etc.
                    if ($this->isUnwantedEntity($nombre)) {
                        $this->line("   ⏭️  Saltando: $nombre ($wikidataId) - tipo no deseado");
                        continue;
                    }

                    $countryName = $item['countryLabel']['value'] ?? null;
                    $pais = \App\Models\Pais::whereRaw('LOWER(nombre) = ?', [mb_strtolower($countryName, 'UTF-8')])->first();

                    Keyword::updateOrCreate(
                        ['wikidata_id' => $wikidataId],
                        [
                            'nombre' => $nombre,
                            'pais_id' => $pais?->id,
                            'tipo' => $tipo
                        ]
                    );

                    $count++;
                    $totalProcessed++;
                }

                $this->line("   ✅ Procesados: $count registros");

                if (count($bindings) < $limit) {
                    $hasMore = false;
                } else {
                    $offset += $limit;
                    sleep(1); // Esperar 1 segundo entre peticiones
                }
            }
        }

        if (!$this->option('skip-cleanup')) {
            $this->call('cleanup:keywords-aliases');
        }

        $this->info("\n✅ Importación completada. Total: $totalProcessed keywords.");
    }

    /**
     * Construye el filtro SPARQL para excluir tipos no deseados
     */
    protected function buildExcludeFilter(): string
    {
        $excludeConditions = [];

        // Excluir tipos específicos de Wikidata
        foreach ($this->excludeTypes as $excludeType) {
            $excludeConditions[] = "?item wdt:P31 $excludeType";
        }

        // Excluir items que sean "instancia de" algo que sea un vehículo, nave, etc.
        $excludeConditions[] = "?item wdt:P31 wd:Q2502701";  // vehículo de motor
        $excludeConditions[] = "?item wdt:P31 wd:Q18608898"; // animal doméstico
        $excludeConditions[] = "?item wdt:P31 wd:Q7889";     // obra de arte
        $excludeConditions[] = "?item wdt:P31 wd:Q5399426";  // artefacto cultural

        if (empty($excludeConditions)) {
            return '';
        }

        $filter = "FILTER NOT EXISTS {\n    { " . implode(" } UNION {\n    ", $excludeConditions) . " }\n  }";
        return $filter;
    }

    /**
     * Construye filtros SPARQL específicos para lugares históricos y ciudades
     */
    protected function buildLocationFilter(string $tipo): string
    {
        if ($tipo === 'arqueologico') {
            // Para lugares arqueológicos: requieren estar en un sitio específico
            return "
  # Filtros para lugares arqueológicos
  FILTER EXISTS { ?item wdt:P625 ?coords }  # Debe tener coordenadas geográficas
  OPTIONAL { ?item wdt:P131 ?location }     # Ubicación administrativa (región, país, etc.)
            ";
        } elseif ($tipo === 'ciudad') {
            // Para ciudades: deben tener ubicación y población
            return "
  # Filtros para ciudades
  FILTER EXISTS { ?item wdt:P625 ?coords }      # Debe tener coordenadas geográficas
  OPTIONAL { ?item wdt:P1566 ?geonamesId }      # ID de Geonames para validar
  OPTIONAL { ?item wdt:P1104 ?population }      # Debe tener población documentada
            ";
        } else {
            // Para pueblos: también requieren coordenadas
            return "
  # Filtros para pueblos
  FILTER EXISTS { ?item wdt:P625 ?coords }  # Debe tener coordenadas geográficas
            ";
        }
    }

    /**
     * Detecta si una entidad no es útil basándose en el nombre
     * Se enfoca en lugares históricos y ciudades relevantes
     */
    protected function isUnwantedEntity(string $nombre): bool
    {
        $nombre = mb_strtolower($nombre, 'UTF-8');

        // Patrones regex para excluir
        $unwantedPatterns = [
            '/\b(HMCS|HMS|USS|INS|IRIS)\b/i',           // Barcos militares
            '/\b(barco|ship|nave|vessel|buque|vela)\b/i', // Palabras de barcos
            '/\s+\(S\d+\)$/i',                            // Patrón "(S73)" - códigos militares
            '/\s+\(D\d+\)$/i',                            // Patrón "(D73)" - destructores
            '/\b(submarino|submarine)\b/i',              // Submarinos
            '/\b(crucero|portaaviones|destructor|fragata|corbeta)/i', // Tipos de barcos
            '/\b(clase|class|modelo|model)\s+(de|of)/i', // Clases o modelos
            '/^[A-Z]{2,}\s*\d{2,}$/i',                    // Códigos como "HMS 123"
            '/\b(apellido|surname|family\s+name)\b/i',   // Apellidos
            '/\b(orden|family|género|genus|especie|species)\b/i', // Taxonomía
            '/\b(evento|event|batalla|battle|guerra|war)\b/i', // Eventos históricos (no lugares)
            '/\b(película|film|libro|book|novela|novel)\b/i', // Obras de ficción
            '/\b(empresa|company|corporación|corporation|marca|brand)\b/i', // Empresas
            '/\b(persona|person|figura|figure|héroe|hero)\b/i', // Personas
        ];

        foreach ($unwantedPatterns as $pattern) {
            if (preg_match($pattern, $nombre)) {
                return true;
            }
        }

        // Excluir por palabras clave específicas
        foreach ($this->excludeKeywords as $keyword) {
            if (stripos($nombre, $keyword) !== false) {
                return true;
            }
        }

        // Excluir nombres que parecen códigos o IDs
        if (preg_match('/^\w+[\s\-]?[0-9]{3,}$/', $nombre)) {
            return true;
        }

        // Excluir si el nombre es muy corto (probable código)
        if (strlen($nombre) < 3 && !ctype_alpha($nombre)) {
            return true;
        }

        

        return false;
    }
}
