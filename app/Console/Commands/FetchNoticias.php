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

        $temas = [
            'Arqueologia' => 'CAAqIAgKIhpDQkFTRFFvSEwyMHZNR2cyTVJJQ1pYTW9BQVAB',
            'Historia' => 'CAAqIQgKIhtDQkFTRGdvSUwyMHZNRE5uTTNjU0FtVnpLQUFQAQ'
        ];

        foreach ($queries as $query) {

            $this->info("Buscando: {$query}");

            $cacheKey = "serpapi_news_" . md5($query);

            $response = Cache::remember($cacheKey, 3600, function () use ($service, $query) {
                return $service->getGoogleNews($query);
            });

            $items = data_get($response, 'news_results', []);

            foreach ($items as $news) {

                $originalLink = $news['link'] ?? null;
                if (!$originalLink) continue;

                // limpiar tracking
                $link = strtok($originalLink, '?');
                if (!$link) continue;

                $externalId = md5($this->canonicalUrl($link));

                $title = $news['title'] ?? 'Sin título';

                $descripcion = $news['snippet'] ?? null;

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

                $contentHash = md5(mb_strtolower(trim($title . ' ' . $descripcion), 'UTF-8'));

                $noticia = Noticia::where('hash', $contentHash)
                    ->orWhere(function ($query) use ($externalId) {
                        $query->where('external_id', $externalId)
                            ->where('source', 'serpapi');
                    })
                    ->first();

                $data = [
                    'hash' => $contentHash,
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
                ];

                try {
                    if ($noticia) {
                        $noticia->update(array_merge($data, [
                            'external_id' => $externalId,
                            'source' => 'serpapi',
                        ]));
                    } else {
                        Noticia::create(array_merge($data, [
                            'external_id' => $externalId,
                            'source' => 'serpapi',
                        ]));
                    }

                    $this->info("Guardada: {$title}");
                } catch (\Throwable $e) {
                    $this->error($e->getMessage());
                }
            }
        }

        $this->info('Fetch completado. Noticias totales: ' . Noticia::count());
    }

    private function canonicalUrl(string $url): string
    {
        $url = trim($url);

        $parts = parse_url($url);
        if (!$parts || !isset($parts['host'])) {
            return $url;
        }

        $scheme = isset($parts['scheme']) ? strtolower($parts['scheme']) : 'http';
        $host = strtolower($parts['host']);
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '';
        $path = rtrim($path, '/');
        $path = $path === '' ? '/' : $path;

        return $scheme . '://' . $host . $port . $path;
    }
}
