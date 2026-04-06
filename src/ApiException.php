<?php

declare(strict_types=1);

namespace EGroupAI\AiSandboxSdk;

final class ApiException extends \RuntimeException
{
    public function __construct(
        public readonly int $statusCode,
        public readonly string $responseBody,
        public readonly ?string $traceId = null
    ) {
        $msg = $traceId === null || $traceId === ''
            ? "HTTP {$statusCode}: {$responseBody}"
            : "HTTP {$statusCode}: {$responseBody} (trace_id={$traceId})";
        parent::__construct($msg);
    }
}
