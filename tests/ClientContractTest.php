<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/HttpRetryPolicy.php';
require_once dirname(__DIR__) . '/src/ApiException.php';
require_once dirname(__DIR__) . '/src/AiSandboxClient.php';

use EGroupAI\AiSandboxSdk\AiSandboxClient;
use EGroupAI\AiSandboxSdk\ApiException;

final class ContractTestError extends RuntimeException
{
}

function assertCondition(bool $condition, string $message): void
{
    if (!$condition) {
        throw new ContractTestError($message);
    }
}

function readJsonFile(string $path): array
{
    $raw = file_get_contents($path);
    if (!is_string($raw)) {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function findFreePort(): int
{
    $server = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
    if ($server === false) {
        throw new ContractTestError("Unable to allocate port: {$errno} {$error}");
    }
    $name = stream_socket_get_name($server, false);
    fclose($server);
    if (!is_string($name)) {
        throw new ContractTestError('Unable to inspect allocated port.');
    }
    $parts = explode(':', $name);
    return (int) end($parts);
}

function startFixtureServer(string $routerPath, string $statePath): array
{
    $port = findFreePort();
    $cmd = escapeshellarg(PHP_BINARY) . ' -S 127.0.0.1:' . $port . ' ' . escapeshellarg($routerPath);
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $env = $_ENV;
    $env['AI_SDK_PHP_CONTRACT_STATE'] = $statePath;
    $proc = proc_open($cmd, $descriptorSpec, $pipes, dirname($routerPath), $env);
    if (!is_resource($proc)) {
        throw new ContractTestError('Failed to start PHP fixture server.');
    }

    $baseUrl = 'http://127.0.0.1:' . $port;
    $ready = false;
    for ($i = 0; $i < 20; $i++) {
        $ctx = stream_context_create(['http' => ['timeout' => 0.2]]);
        @file_get_contents($baseUrl . '/__health', false, $ctx);
        if (proc_get_status($proc)['running']) {
            $ready = true;
            break;
        }
        usleep(100000);
    }
    if (!$ready) {
        throw new ContractTestError('Fixture server failed to become ready.');
    }

    return [$proc, $pipes, $baseUrl];
}

function stopFixtureServer($proc, array $pipes): void
{
    foreach ($pipes as $pipe) {
        if (is_resource($pipe)) {
            fclose($pipe);
        }
    }
    if (is_resource($proc)) {
        proc_terminate($proc);
        proc_close($proc);
    }
}

$statePath = tempnam(sys_get_temp_dir(), 'ai-sdk-php-contract-');
if (!is_string($statePath)) {
    fwrite(STDERR, "Unable to create temporary state file.\n");
    exit(1);
}
file_put_contents($statePath, json_encode(['agents_get_calls' => 0, 'chat_post_calls' => 0]));

$routerPath = __DIR__ . '/fixtures/router.php';
[$proc, $pipes, $baseUrl] = startFixtureServer($routerPath, $statePath);

try {
    $client = new AiSandboxClient($baseUrl, 'test-key', 1, 2);
    $result = $client->listAgents();
    assertCondition(($result['ok'] ?? false) === true, 'GET contract should eventually succeed.');
    $state = readJsonFile($statePath);
    assertCondition((int) ($state['agents_get_calls'] ?? 0) === 2, 'GET should be attempted exactly twice.');

    $postError = null;
    try {
        $client->sendChat(123, ['channelId' => 'c-1', 'message' => 'hello']);
    } catch (ApiException $e) {
        $postError = $e;
    }
    assertCondition($postError instanceof ApiException, 'POST 5xx should raise ApiException.');
    assertCondition($postError->statusCode === 503, 'POST failure should keep status code.');
    assertCondition($postError->traceId === 'trace-post-1', 'POST failure should preserve trace id.');
    $state = readJsonFile($statePath);
    assertCondition((int) ($state['chat_post_calls'] ?? 0) === 1, 'POST 5xx should not be retried.');
} catch (Throwable $e) {
    fwrite(STDERR, "ClientContractTest failed: {$e->getMessage()}\n");
    stopFixtureServer($proc, $pipes);
    @unlink($statePath);
    exit(1);
}

stopFixtureServer($proc, $pipes);
@unlink($statePath);
echo "ClientContractTest OK\n";
