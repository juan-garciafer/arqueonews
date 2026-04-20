<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SerpApiService
{
    protected string $baseUrl = 'https://serpapi.com/search.json';

    public function getGoogleNews(string $query)
    {
        $response = Http::get($this->baseUrl, [
            'engine' => 'google_news',
            'q' => $query,
            'api_key' => config('services.serpapi.key'),
            'hl' => 'es',
            'gl' => 'es',
        ]);

        return $response->json();
    }
}
