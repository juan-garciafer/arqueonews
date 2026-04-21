<?php

namespace App\Console\Commands;

use App\Models\Noticia;
use App\Services\SerpApiService;
use App\Services\NewsScraperService;
use App\Services\PaisDetectorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FetchNoticias extends Command
{
    protected $signature = 'app:fetch-noticias';
    protected $description = 'Fetch noticias arqueológicas desde SerpAPI';

    protected PaisDetectorService $paisDetector;

    // public function __construct(PaisDetectorService $paisDetector)
    // {
    //     parent::__construct();
    //     $this->paisDetector = $paisDetector;
    // }

    public function handle()
    {
        
        $this->info('Iniciando fetch SerpApi...');

        $this->paisDetector = app(PaisDetectorService::class);

        $service = app(SerpApiService::class);
        $scraper = app(NewsScraperService::class);

        // 🔥 QUERIES OPTIMIZADAS
        $queries = [
            'arqueología',
            'arqueología romana',
            'excavación arqueológica',
            'yacimiento arqueológico',
            'patrimonio histórico',
            'ruinas antiguas',
            'prehistoria hallazgo',
            'descubrimiento arqueológico',
        ];

        foreach ($queries as $query) {

            $this->info("Buscando: {$query}");

            $cacheKey = "serpapi_news_" . md5($query);

            $response = Cache::remember($cacheKey, 3600, function () use ($service, $query) {
                return $service->getGoogleNews($query, [
                    'hl' => 'es',
                    'gl' => 'es',
                    'so' => 1,
                ]);
            });

            $items = data_get($response, 'news_results', []);

            foreach ($items as $news) {

                $originalLink = $news['link'] ?? null;
                if (!$originalLink) continue;

                // limpiar tracking
                $link = strtok($originalLink, '?');
                if (!$link) continue;

                $externalId = md5($link);

                // evitar duplicados
                // if (Noticia::where('external_id', $externalId)->exists()) {
                //     continue;
                // }

                $title = $news['title'] ?? 'Sin título';

                // snippet
                $descripcion = $news['snippet'] ?? null;

                // scraping si no hay snippet
                if (!$descripcion) {
                    $descripcion = Cache::remember(
                        "desc_{$externalId}",
                        86400,
                        fn() => $scraper->getDescripcion($link)
                    );
                }

                $descripcion = $descripcion ?? $title;

                // 🔥 DETECCIÓN DE PAÍS (CORREGIDO)
                $text = $title . '. ' . $descripcion;
                $pais = $this->paisDetector->detectarPais($text);

                $source = data_get($news, 'source.name', 'general');
                $thumbnail = $news['thumbnail'] ?? null;
                $publishedAt = $news['iso_date'] ?? null;

                try {
                    Noticia::updateOrCreate(
                        [
                            'external_id' => $externalId,
                            'source' => 'serpapi',
                        ],
                        [
                            'titulo' => $title,
                            'descripcion' => $descripcion,
                            'url_noticia' => $link,
                            'url_imagen' => $thumbnail,
                            'fecha_publicacion' => $publishedAt
                                ? date('Y-m-d H:i:s', strtotime($publishedAt))
                                : now(),
                            'categoria' => 'arqueología',
                            'pais' => $pais?->nombre,
                            'codigo_pais' => $pais?->codigo_iso ?? null,
                        ]
                    );

                    $this->info("Guardada: {$title}");
                } catch (\Throwable $e) {
                    $this->error($e->getMessage());
                }
            }
        }

        $this->info('Fetch completado');
    }
}
