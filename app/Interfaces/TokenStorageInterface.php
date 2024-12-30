<?php

namespace App\Interfaces;

interface TokenStorageInterface
{
    public function store(string $token, int $userId): void;
    public function retrieve(int $userId): ?array;
}