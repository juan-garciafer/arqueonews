<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FeedlyService
{
    protected string $baseUrl;
    protected string $accessToken;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('FEEDLY_BASE_URL', 'https://cloud.feedly.com/v3'), '/');
        $this->accessToken = env('FEEDLY_ACCESS_TOKEN');
    }

    /**
     * Cliente HTTP base con auth
     */
    protected function client()
    {
        return Http::withToken($this->accessToken)
            ->acceptJson()
            ->timeout(20);
    }

    /**
     * Obtener streams de noticias
     * Ej: global.all o categoría específica
     */
    public function getStreams(string $streamId = 'user/-/category/global.all', int $count = 50)
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/streams/contents", [
                'streamId' => $streamId,
                'count' => $count,
            ]);

            if ($response->failed()) {
                Log::error('Feedly API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [];
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('Feedly exception', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Obtener solo items limpios
     */
    public function getItems(string $streamId = 'user/-/category/global.all')
    {
        $data = $this->getStreams($streamId);

        return $data['items'] ?? [];
    }

    /**
     * Obtener detalle de un artículo (opcional)
     */
    public function getEntry(string $entryId)
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/entries/{$entryId}");

            return $response->successful()
                ? $response->json()
                : null;
        } catch (\Throwable $e) {
            Log::error('Feedly entry error', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Listar categorías del usuario
     */
    public function getCategories()
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/categories");

            return $response->successful()
                ? $response->json()
                : [];
        } catch (\Throwable $e) {
            Log::error('Feedly categories error', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Streams por categoría
     */
    public function getCategoryStreams(string $categoryId, int $count = 50)
    {
        return $this->getStreams("user/-/category/{$categoryId}", $count);
    }
}
