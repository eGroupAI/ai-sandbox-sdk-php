<?php

declare(strict_types=1);

namespace EGroupAI\AiSandboxSdk;

final class HttpRetryPolicy
{
    /** Retry 429/5xx only for GET/HEAD to avoid duplicate write side effects. */
    public static function shouldRetryTransientHttpStatus(string $method, int $status): bool
    {
        if ($status !== 429 && ($status < 500 || $status > 599)) {
            return false;
        }
        $m = strtoupper(trim($method));
        return $m === "GET" || $m === "HEAD";
    }

    public static function getRetryDelayMicros(int $attempt): int
    {
        $safeAttempt = max(1, $attempt);
        $delayMs = 200 * (2 ** ($safeAttempt - 1));
        $cappedMs = min(2000, $delayMs);
        return (int) ($cappedMs * 1000);
    }
}
