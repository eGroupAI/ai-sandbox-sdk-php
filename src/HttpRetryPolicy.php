<?php

declare(strict_types=1);

namespace EGroupAI\AiSandboxSdk;

final class HttpRetryPolicy
{
    /** 僅對 GET/HEAD 在 429 或 5xx 時建議自動重試，避免寫入重複副作用。 */
    public static function shouldRetryTransientHttpStatus(string $method, int $status): bool
    {
        if ($status !== 429 && ($status < 500 || $status > 599)) {
            return false;
        }
        $m = strtoupper(trim($method));
        return $m === "GET" || $m === "HEAD";
    }
}
