<?php

namespace App\Services;

use App\Models\Client;

class ClientService
{
    public function create(array $data): Client
    {
        $data = $this->normalize($data);

        return Client::create($data);
    }

    public function update(Client $client, array $data): Client
    {
        $data = $this->normalize($data);
        $client->update($data);

        return $client;
    }

    /**
     * Normaliza dados antes de persistir (documento sem máscara, UF em maiúsculas).
     */
    private function normalize(array $data): array
    {
        if (isset($data['document'])) {
            $data['document'] = preg_replace('/\D/', '', $data['document']);
        }

        if (isset($data['state'])) {
            $data['state'] = strtoupper(trim($data['state']));
        }

        if (isset($data['zip_code'])) {
            $data['zip_code'] = preg_replace('/\D/', '', $data['zip_code']);
        }

        return $data;
    }
}
