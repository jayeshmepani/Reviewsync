<?php

namespace App\Services;

use App\Interfaces\TokenStorageInterface;
use Illuminate\Support\Facades\Cache;

class TokenStorage implements TokenStorageInterface
{
    private const TOKEN_PREFIX = 'google_token_';

    public function store(string $token, int $userId): void
    {
        Cache::put(
            $this->getKey($userId),
            $token,
            now()->addSeconds($token['expires_in'] ?? 3600)
        );
    }

    public function retrieve(int $userId): ?array
    {
        return Cache::get($this->getKey($userId));
    }

    private function getKey(int $userId): string
    {
        return self::TOKEN_PREFIX . $userId;
    }
}