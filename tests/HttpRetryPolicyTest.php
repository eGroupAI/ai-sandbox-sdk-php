<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/HttpRetryPolicy.php';

use EGroupAI\AiSandboxSdk\HttpRetryPolicy;

$ok = true;
$ok = $ok && HttpRetryPolicy::shouldRetryTransientHttpStatus('GET', 503);
$ok = $ok && !HttpRetryPolicy::shouldRetryTransientHttpStatus('POST', 503);
$ok = $ok && !HttpRetryPolicy::shouldRetryTransientHttpStatus('GET', 404);

if (!$ok) {
    fwrite(STDERR, "HttpRetryPolicyTest failed\n");
    exit(1);
}

echo "HttpRetryPolicyTest OK\n";
