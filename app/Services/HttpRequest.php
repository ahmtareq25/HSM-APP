<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HttpRequest
{
    public bool $is_success = false;
    public array $body;

    public function postAsFrom(string $url, array $payload, array $headers = []): static
    {

        return $this->prepareResponse(
            Http::asForm()
                ->withHeaders($headers)
                ->post($url, $payload)
        );
    }

    public function post(string $url, array $payload, array $headers = []): static
    {
        return $this->prepareResponse(
            Http::withHeaders($headers)
                ->post($url, $payload)
        );
    }

    public function get(string $url, array $queries = [], array $headers = []): static
    {
        return $this->prepareResponse(
            Http::withHeaders($headers)
                ->get($url)
        );
    }

    private function prepareResponse($response): static
    {
        $this->is_success = $response->successful();
        if($this->is_success) {
            $this->body = json_decode($response->body(), true);
        }
        return $this;
    }
}
