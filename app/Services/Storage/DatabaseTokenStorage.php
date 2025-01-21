<?php

namespace App\Services\Storage;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DatabaseTokenStorage
{
    private const CACHE_KEY_PREFIX = 'google_token_';

    public function store(array $token, int $userId): void
    {
        try {
            User::findOrFail($userId)->update([
                'google_token' => encrypt(json_encode($token)),
            ]);
            Cache::put($this->getCacheKey($userId), $token, now()->addDay());
        } catch (Exception $e) {
            throw new Exception('Failed to store token: '.$e->getMessage());
        }
    }

    public function retrieve(int $userId): ?array
    {
        $cachedToken = Cache::get($this->getCacheKey($userId));
        if ($cachedToken !== null) {
            return $cachedToken;
        }

        try {
            $user = User::findOrFail($userId);
            if (! $user->google_token) {
                return null;
            }

            $token = json_decode(decrypt($user->google_token), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->delete($userId);

                return null;
            }

            Cache::put($this->getCacheKey($userId), $token, now()->addDay());

            return $token;
        } catch (Exception $e) {
            Log::error('Failed to retrieve token', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function delete(int $userId): void
    {
        try {
            User::findOrFail($userId)->update(['google_token' => null]);
            Cache::forget($this->getCacheKey($userId));
        } catch (Exception $e) {
            Log::error('Failed to delete token', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getCacheKey(int $userId): string
    {
        return self::CACHE_KEY_PREFIX.$userId;
    }
}
