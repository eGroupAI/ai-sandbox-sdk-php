<?php

declare(strict_types=1);

namespace EGroupAI\AiSandboxSdk;

final class ApiException extends \RuntimeException
{
    public function __construct(
        public readonly int $statusCode,
        public readonly string $responseBody
    ) {
        parent::__construct("HTTP {$statusCode}: {$responseBody}");
    }
}
