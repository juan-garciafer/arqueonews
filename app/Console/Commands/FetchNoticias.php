<?php

namespace App\Console\Commands;

use App\Models\Noticia;
use App\Services\SerpApiService;
use App\Services\NewsScraperService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FetchNoticias extends Command
{
    protected $signature = 'app:fetch-noticias';
    protected $description = 'Fetch noticias arqueológicas desde SerpAPI';

    public function handle()
    {
        $this->info('Iniciando fetch SerpApi...');

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

                // ❌ evitar duplicados rápidos
                if (Noticia::where('external_id', $externalId)->exists()) {
                    continue;
                }

                $title = $news['title'] ?? 'Sin título';

                // 🟢 snippet
                $descripcion = $news['snippet'] ?? null;

                // 🟢 scraping si no hay snippet
                if (!$descripcion) {
                    $descripcion = Cache::remember(
                        "desc_{$externalId}",
                        86400,
                        fn() => $scraper->getDescripcion($link)
                    );
                }

                $descripcion = $descripcion ?? $title;

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
                            'categoria' => $source,
                            'pais' => null,
                            'codigo_pais' => null,
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

// namespace App\Console\Commands;

// use App\Models\Noticia;
// use App\Services\SerpApiService;
// use App\Services\NewsScraperService;
// use Illuminate\Console\Command;
// use Illuminate\Support\Facades\Cache;

// class FetchNoticias extends Command
// {
//     protected $signature = 'app:fetch-noticias';

//     protected $description = 'Fetch noticias desde SerpApi';

//     public function handle()
//     {
//         $this->info('Iniciando fetch SerpApi...');

//         $service = app(SerpApiService::class);
//         $scraper = app(NewsScraperService::class);



//         $items = data_get($service->getGoogleNews('arqueología'), 'news_results', []);

//         foreach ($items as $news) {

//             $originalLink = $news['link'] ?? null;

//             if (!$originalLink) {
//                 continue;
//             }

//             // 🔥 limpiar tracking UNA sola vez
//             $link = strtok($originalLink, '?');

//             if (!$link) {
//                 continue;
//             }

//             $externalId = md5($link);

//             $title = $news['title'] ?? 'Sin título';

//             // 🟢 1. intentar snippet
//             $descripcion = $news['snippet'] ?? null;

//             // 🟢 2. si no hay snippet → cache + scraping
//             if (!$descripcion) {
//                 $descripcion = Cache::remember(
//                     "desc_{$externalId}",
//                     86400, // 24h
//                     function () use ($scraper, $link) {
//                         return $scraper->getDescripcion($link);
//                     }
//                 );
//             }

//             // 🟢 3. fallback final
//             $descripcion = $descripcion ?? $title;

//             $source = data_get($news, 'source.name', 'general');
//             $thumbnail = $news['thumbnail'] ?? null;
//             $publishedAt = $news['iso_date'] ?? null;

//             try {

//                 Noticia::updateOrCreate(
//                     [
//                         'external_id' => $externalId,
//                         'source' => 'serpapi',
//                     ],
//                     [
//                         'titulo' => $title,
//                         'descripcion' => $descripcion,
//                         'url_noticia' => $link,
//                         'url_imagen' => $thumbnail,
//                         'fecha_publicacion' => $publishedAt
//                             ? date('Y-m-d H:i:s', strtotime($publishedAt))
//                             : now(),
//                         'categoria' => $source,
//                         'pais' => null,
//                         'codigo_pais' => null,
//                     ]
//                 );

//                 $this->info("Noticia guardada: {$externalId}");
//             } catch (\Throwable $e) {
//                 $this->error($e->getMessage());
//             }
//         }

//         $this->info('Fetch completado');
//     }
// }
