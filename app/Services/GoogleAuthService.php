<?php

namespace App\Services;

use App\Services\Storage\DatabaseTokenStorage;
use Exception;
use Google\Client;

class GoogleAuthService
{
    private const TOKEN_EXPIRY_BUFFER = 300;

    private Client $client;

    public function __construct(
        private ?int $userId = null,
        private $tokenStorage = new DatabaseTokenStorage
    ) {
        $this->initClient();
        $this->loadStoredToken();
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function createAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function fetchToken(string $code): array
    {
        try {
            $token = $this->client->fetchAccessTokenWithAuthCode($code);

            if (!empty($token['error'])) {
                throw new Exception('Authentication failed: ' . $token['error']);
            }

            $this->validateAndStoreToken($token);

            return $token;

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function isTokenExpired(): bool
    {
        $token = $this->client->getAccessToken();
        if (empty($token)) {
            return true;
        }

        $expiresAt = $token['created'] + $token['expires_in'] - self::TOKEN_EXPIRY_BUFFER;

        return time() >= $expiresAt;
    }

    public function refreshToken(): array
    {
        try {
            if (!$this->client->getRefreshToken()) {
                throw new Exception('No refresh token available');
            }

            $token = $this->client->fetchAccessTokenWithRefreshToken();

            if (!empty($token['error'])) {
                throw new Exception('Token refresh failed: ' . $token['error']);
            }

            $this->validateAndStoreToken($token);

            return $token;

        } catch (Exception $e) {
            throw $e;
        }
    }

    public function ensureToken(): ?array
    {
        if (!$this->client->getAccessToken()) {
            return null;
        }

        if ($this->isTokenExpired()) {
            try {
                return $this->refreshToken();
            } catch (Exception $e) {
                return null;
            }
        }

        return $this->client->getAccessToken();
    }

    protected function initClient()
    {
        $this->client = new Client;
        $this->client->setClientId(config('google.client_id'));
        $this->client->setClientSecret(config('google.client_secret'));
        $this->client->setRedirectUri(config('google.redirect_uri'));
        $this->client->addScope([
            'https://www.googleapis.com/auth/business.manage',
            'https://www.googleapis.com/auth/userinfo.profile',
            'https://www.googleapis.com/auth/userinfo.email'
        ]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    protected function loadStoredToken(): void
    {
        if ($this->userId) {
            $token = $this->tokenStorage->retrieve($this->userId);
            if ($token) {
                $this->client->setAccessToken($token);
            }
        }
    }

    public function validateAndStoreToken(array $token): void
    {
        if (!$this->userId) {
            throw new Exception('User ID is required to store token');
        }

        if (!isset($token['access_token'])) {
            throw new Exception('Invalid token format: missing access_token');
        }

        $this->tokenStorage->store($token, $this->userId);
    }
}
