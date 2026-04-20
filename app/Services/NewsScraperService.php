<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NewsScraperService
{
    public function getDescripcion(string $url): ?string
    {
        try {
            $html = Http::timeout(10)->get($url)->body();

            libxml_use_internal_errors(true);

            $dom = new \DOMDocument();
            $dom->loadHTML($html);

            $xpath = new \DOMXPath($dom);

            // 🔥 OG DESCRIPTION (prioridad)
            $og = $xpath->query("//meta[@property='og:description']");

            if ($og->length > 0) {
                return $og[0]->getAttribute('content');
            }

            // 🔥 META DESCRIPTION fallback
            $meta = $xpath->query("//meta[@name='description']");

            if ($meta->length > 0) {
                return $meta[0]->getAttribute('content');
            }

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
