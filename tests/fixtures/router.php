<?php

declare(strict_types=1);

/**
 * Lightweight fixture router for PHP contract tests.
 *
 * State is persisted in a JSON file so retries can be asserted.
 */
function loadState(string $path): array
{
    if (!is_file($path)) {
        return [
            'agents_get_calls' => 0,
            'chat_post_calls' => 0,
        ];
    }
    $raw = file_get_contents($path);
    $decoded = is_string($raw) ? json_decode($raw, true) : null;
    if (!is_array($decoded)) {
        return [
            'agents_get_calls' => 0,
            'chat_post_calls' => 0,
        ];
    }
    return $decoded;
}

function saveState(string $path, array $state): void
{
    file_put_contents($path, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$stateFile = getenv('AI_SDK_PHP_CONTRACT_STATE');
if (!is_string($stateFile) || $stateFile === '') {
    http_response_code(500);
    echo 'missing state file';
    return;
}

$state = loadState($stateFile);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH) ?: '/';

if ($method === 'GET' && $path === '/api/v1/agents') {
    $state['agents_get_calls'] = (int) ($state['agents_get_calls'] ?? 0) + 1;
    saveState($stateFile, $state);

    if ($state['agents_get_calls'] === 1) {
        http_response_code(503);
        echo 'temporary failure';
        return;
    }

    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'payload' => ['items' => []]], JSON_UNESCAPED_SLASHES);
    return;
}

if ($method === 'POST' && $path === '/api/v1/agents/123/chat') {
    $state['chat_post_calls'] = (int) ($state['chat_post_calls'] ?? 0) + 1;
    saveState($stateFile, $state);

    header('x-trace-id: trace-post-1');
    http_response_code(503);
    echo 'write failed';
    return;
}

http_response_code(404);
echo 'not found';
