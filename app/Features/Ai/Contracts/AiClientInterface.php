<?php

namespace App\Features\Ai\Contracts;

use App\Features\Ai\DTO\GeneratedPostItem;

interface AiClientInterface
{
    /** @return list<GeneratedPostItem> */
    public function generate(int $count, ?string $topic = null): array;
}
