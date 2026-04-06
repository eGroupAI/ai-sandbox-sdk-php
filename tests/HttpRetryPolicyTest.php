<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/HttpRetryPolicy.php';

use EGroupAI\AiSandboxSdk\HttpRetryPolicy;

$ok = true;
$ok = $ok && HttpRetryPolicy::shouldRetryTransientHttpStatus('GET', 503);
$ok = $ok && !HttpRetryPolicy::shouldRetryTransientHttpStatus('POST', 503);
$ok = $ok && !HttpRetryPolicy::shouldRetryTransientHttpStatus('GET', 404);
$ok = $ok && HttpRetryPolicy::getRetryDelayMicros(1) === 200000;
$ok = $ok && HttpRetryPolicy::getRetryDelayMicros(2) === 400000;
$ok = $ok && HttpRetryPolicy::getRetryDelayMicros(3) === 800000;
$ok = $ok && HttpRetryPolicy::getRetryDelayMicros(4) === 1600000;
$ok = $ok && HttpRetryPolicy::getRetryDelayMicros(5) === 2000000;
$ok = $ok && HttpRetryPolicy::getRetryDelayMicros(8) === 2000000;

if (!$ok) {
    fwrite(STDERR, "HttpRetryPolicyTest failed\n");
    exit(1);
}

echo "HttpRetryPolicyTest OK\n";
