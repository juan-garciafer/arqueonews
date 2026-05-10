<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Keyword;
use App\Models\Alias;

class CleanupKeywordsAndAliases extends Command
{
    protected $signature = 'cleanup:keywords-aliases {--dry-run : Simular limpieza sin eliminar}';
    protected $description = 'Limpia keywords y aliases inútiles de forma segura';

    public function handle()
    {
        $this->info("🔍 Analizando registros...\n");

        // 1. Keywords sin país
        $keywordsNoCountry = Keyword::whereNull('pais_id')->count();
        $this->line("❌ Keywords sin país asignado: <fg=red>$keywordsNoCountry</>");

        // 2. Aliases muy cortos
        $aliasesCortos = Alias::where('nombre', '<', 3)->count();
        $this->line("❌ Aliases muy cortos (< 3 caracteres): <fg=red>$aliasesCortos</>");

        // 3. Aliases vacíos o solo espacios
        $aliasesVacios = Alias::where('nombre', 'REGEXP', '^\\s*$')->count();
        $this->line("❌ Aliases vacíos o solo espacios: <fg=red>$aliasesVacios</>");

        // 4. Keywords duplicadas por wikidata_id
        $duplicadas_query = DB::table('keywords')
            ->whereNotNull('wikidata_id')
            ->groupBy('wikidata_id')
            ->havingRaw('COUNT(*) > 1')
            ->select('wikidata_id')
            ->get();
        $duplicadas = $duplicadas_query->count();
        $this->line("❌ Keywords duplicadas por wikidata_id: <fg=red>$duplicadas</>");

        // 5. Keywords con nombres sospechosos (barcos, vehículos, etc.)
        $unwantedKeywords = $this->countUnwantedKeywords();
        if ($unwantedKeywords > 0) {
            $this->line("⚠️  Keywords sospechosos (barcos, vehículos, etc.): <fg=yellow>$unwantedKeywords</>");
        }

        $total = $keywordsNoCountry + $aliasesCortos + $aliasesVacios + $unwantedKeywords;

        if ($total === 0 && $duplicadas === 0) {
            $this->info("\n✅ La base de datos está limpia, no hay registros inútiles.");
            return;
        }

        $this->newLine();

        if ($this->option('dry-run')) {
            $this->info("🏃 Modo DRY-RUN: se muestran cambios pero NO se elimina nada");
            $this->newLine();
        }

        if ($this->confirm('¿Deseas proceder con la limpieza?', false)) {

            // Limpiar keywords sin país
            if ($keywordsNoCountry > 0) {
                if (!$this->option('dry-run')) {
                    $deleted = Keyword::whereNull('pais_id')->delete();
                    $this->info("✅ Eliminadas $deleted keywords sin país");
                } else {
                    $this->info("🏃 [DRY-RUN] Se eliminarían $keywordsNoCountry keywords sin país");
                }
            }

            // Limpiar aliases muy cortos
            if ($aliasesCortos > 0) {
                if (!$this->option('dry-run')) {
                    $deleted = Alias::where('nombre', '<', 3)->delete();
                    $this->info("✅ Eliminados $deleted aliases muy cortos");
                } else {
                    $this->info("🏃 [DRY-RUN] Se eliminarían $aliasesCortos aliases muy cortos");
                }
            }

            // Limpiar aliases vacíos
            if ($aliasesVacios > 0) {
                if (!$this->option('dry-run')) {
                    $deleted = Alias::where('nombre', 'REGEXP', '^\\s*$')->delete();
                    $this->info("✅ Eliminados $deleted aliases vacíos");
                } else {
                    $this->info("🏃 [DRY-RUN] Se eliminarían $aliasesVacios aliases vacíos");
                }
            }

            // Limpiar duplicadas: mantener la primera, eliminar el resto
            if ($duplicadas > 0) {
                $duplicadas_list = DB::table('keywords')
                    ->whereNotNull('wikidata_id')
                    ->groupBy('wikidata_id')
                    ->havingRaw('COUNT(*) > 1')
                    ->select('wikidata_id')
                    ->pluck('wikidata_id');

                $eliminated = 0;
                foreach ($duplicadas_list as $wikidataId) {
                    $ids = Keyword::where('wikidata_id', $wikidataId)
                        ->orderBy('id')
                        ->pluck('id')
                        ->toArray();

                    // Mantener la primera, eliminar el resto
                    array_shift($ids);
                    if (!$this->option('dry-run')) {
                        $eliminated += Keyword::whereIn('id', $ids)->delete();
                    } else {
                        $eliminated += count($ids);
                    }
                }
                
                if (!$this->option('dry-run')) {
                    $this->info("✅ Eliminadas $eliminated keywords duplicadas");
                } else {
                    $this->info("🏃 [DRY-RUN] Se eliminarían $eliminated keywords duplicadas");
                }
            }

            // Limpiar keywords sospechosos
            if ($unwantedKeywords > 0) {
                if ($this->confirm("¿Eliminar $unwantedKeywords keywords sospechosos?", false)) {
                    if (!$this->option('dry-run')) {
                        $eliminated = $this->deleteUnwantedKeywords();
                        $this->info("✅ Eliminados $eliminated keywords sospechosos");
                    } else {
                        $this->info("🏃 [DRY-RUN] Se eliminarían $unwantedKeywords keywords sospechosos");
                    }
                }
            }

            $this->info("\n🎉 Limpieza completada exitosamente.");
            return;
        }

        $this->info("Operación cancelada.");
    }

    protected function countUnwantedKeywords(): int
    {
        $unwantedPatterns = [
            '/\b(HMCS|HMS|USS|INS|IRIS)\b/i',
            '/\b(barco|ship|nave|vessel|buque|vela)\b/i',
            '/\s+\(S\d+\)$/i',
            '/\s+\(D\d+\)$/i',
            '/\b(submarino|submarine)\b/i',
            '/\b(crucero|portaaviones|destructor|fragata|corbeta)/i',
            '/\b(clase|class|modelo|model)\s+(de|of)/i',
            '/^[A-Z]{2,}\s*\d{2,}$/i',
            '/\b(apellido|surname|family\s+name)\b/i',
            '/\b(orden|family|género|genus|especie|species)\b/i',
            '/\b(evento|event|batalla|battle|guerra|war)\b/i',
            '/\b(película|film|libro|book|novela|novel)\b/i',
            '/\b(empresa|company|corporación|corporation|marca|brand)\b/i',
            '/\b(persona|person|figura|figure|héroe|hero)\b/i',
        ];

        $count = 0;
        $keywords = Keyword::all();
        
        foreach ($keywords as $keyword) {
            foreach ($unwantedPatterns as $pattern) {
                if (preg_match($pattern, $keyword->nombre)) {
                    $count++;
                    break;
                }
            }
        }

        return $count;
    }

    protected function deleteUnwantedKeywords(): int
    {
        $unwantedPatterns = [
            '/\b(HMCS|HMS|USS|INS|IRIS)\b/i',
            '/\b(barco|ship|nave|vessel|buque|vela)\b/i',
            '/\s+\(S\d+\)$/i',
            '/\s+\(D\d+\)$/i',
            '/\b(submarino|submarine)\b/i',
            '/\b(crucero|portaaviones|destructor|fragata|corbeta)/i',
            '/\b(clase|class|modelo|model)\s+(de|of)/i',
            '/^[A-Z]{2,}\s*\d{2,}$/i',
            '/\b(apellido|surname|family\s+name)\b/i',
            '/\b(orden|family|género|genus|especie|species)\b/i',
            '/\b(evento|event|batalla|battle|guerra|war)\b/i',
            '/\b(película|film|libro|book|novela|novel)\b/i',
            '/\b(empresa|company|corporación|corporation|marca|brand)\b/i',
            '/\b(persona|person|figura|figure|héroe|hero)\b/i',
        ];

        $deleted = 0;
        $keywords = Keyword::all();
        
        foreach ($keywords as $keyword) {
            foreach ($unwantedPatterns as $pattern) {
                if (preg_match($pattern, $keyword->nombre)) {
                    $this->line("   🗑️  Eliminando: {$keyword->nombre}");
                    $keyword->delete();
                    $deleted++;
                    break;
                }
            }
        }

        return $deleted;
    }
}
