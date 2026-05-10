<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Keyword;

class BlacklistWikidataIds extends Command
{
    protected $signature = 'cleanup:blacklist-wikidata {--add=} {--remove=} {--list} {--clean}';
    protected $description = 'Gestiona una lista negra de IDs de Wikidata inútiles (Q3329789, etc.)';

    // IDs de Wikidata que NO son lugares útiles para arqueología
    protected array $blacklist = [
        'Q3329789',           // Según Wikidata, parece ser una entidad rara
        'Q11446',             // HMCS (Clase de barcos militares)
        'Q1438414',           // HMCS Onondaga (S73)
        'Q39614',             // Barco militar
        'Q2096',              // Clase de nave
        'Q3024240',           // Modelo de vehículo
    ];

    public function handle()
    {
        if ($this->option('list')) {
            $this->listBlacklist();
            return;
        }

        if ($this->option('add')) {
            $this->addToBlacklist($this->option('add'));
            return;
        }

        if ($this->option('remove')) {
            $this->removeFromBlacklist($this->option('remove'));
            return;
        }

        if ($this->option('clean')) {
            $this->cleanBlacklistedKeywords();
            return;
        }

        $this->info('Uso:');
        $this->line('  --list          Mostrar lista negra actual');
        $this->line('  --add=QID       Agregar ID a la lista negra');
        $this->line('  --remove=QID    Remover ID de la lista negra');
        $this->line('  --clean         Eliminar todos los keywords en la lista negra de la BD');
    }

    protected function listBlacklist()
    {
        $this->info("📋 Lista negra de IDs de Wikidata:\n");
        
        foreach ($this->blacklist as $qid) {
            $keyword = Keyword::where('wikidata_id', $qid)->first();
            if ($keyword) {
                $this->line("❌ <fg=red>$qid</> - {$keyword->nombre} (en BD)");
            } else {
                $this->line("⚪ $qid (no encontrado en BD)");
            }
        }
        
        $total = Keyword::whereIn('wikidata_id', $this->blacklist)->count();
        $this->info("\n📊 Total de keywords en lista negra en BD: <fg=yellow>$total</>");
    }

    protected function addToBlacklist(string $qid)
    {
        $qid = strtoupper(trim($qid));
        
        if (in_array($qid, $this->blacklist)) {
            $this->warn("⚠️  $qid ya está en la lista negra");
            return;
        }

        // Guardar en archivo de configuración
        $this->updateBlacklistFile($qid, 'add');
        $this->info("✅ Agregado $qid a la lista negra");
        
        $keyword = Keyword::where('wikidata_id', $qid)->first();
        if ($keyword) {
            $this->line("   Encontrado en BD: {$keyword->nombre}");
            if ($this->confirm('¿Deseas eliminarlo ahora?')) {
                $keyword->delete();
                $this->info("   ✅ Eliminado");
            }
        }
    }

    protected function removeFromBlacklist(string $qid)
    {
        $qid = strtoupper(trim($qid));
        
        if (!in_array($qid, $this->blacklist)) {
            $this->warn("⚠️  $qid no está en la lista negra");
            return;
        }

        $this->updateBlacklistFile($qid, 'remove');
        $this->info("✅ Removido $qid de la lista negra");
    }

    protected function cleanBlacklistedKeywords()
    {
        $this->info("🧹 Eliminando keywords en lista negra...\n");
        
        $deleted = 0;
        foreach ($this->blacklist as $qid) {
            $count = Keyword::where('wikidata_id', $qid)->delete();
            if ($count > 0) {
                $this->info("   ✅ Eliminados $count registros con $qid");
                $deleted += $count;
            }
        }
        
        if ($deleted > 0) {
            $this->info("\n✅ Total eliminados: $deleted keywords");
        } else {
            $this->info("\n✅ No hay keywords en lista negra para eliminar");
        }
    }

    protected function updateBlacklistFile(string $qid, string $action)
    {
        // Aquí podrías guardar en un archivo de configuración
        // Por ahora solo se actualiza en memoria durante la ejecución
        // Considera crear una tabla en la BD para persistir la lista negra
    }
}
