# AI Sandbox SDK for PHP

Official PHP SDK for AI Sandbox v1.

## Install

```bash
composer require egroupai/ai-sandbox-sdk-php
```

## Highlights

- Retry and timeout controls for resilient API calls.
- Full v1 API coverage for agent/chat/knowledge-base operations.
- SSE streaming helper via `sendChatStream(...)`.

## Quick Start

```php
<?php

use EGroupAI\AiSandboxSdk\AiSandboxClient;

$client = new AiSandboxClient(
    baseUrl: "https://www.egroupai.com",
    apiKey: getenv("AI_SANDBOX_API_KEY") ?: ""
);

$agent = $client->createAgent([
    "agentDisplayName" => "Support Bot",
    "agentDescription" => "Handles customer support chats",
]);

$agentId = (int)($agent["payload"]["agentId"] ?? 0);

$channel = $client->createChatChannel($agentId, [
    "title" => "SDK Example",
    "visitorId" => "visitor-001",
]);

$channelId = (string)($channel["payload"]["channelId"] ?? "");

$chunks = $client->sendChatStream($agentId, [
    "channelId" => $channelId,
    "message" => "What is the return policy?",
    "stream" => true,
]);

foreach ($chunks as $chunk) {
    echo $chunk . PHP_EOL;
}
```

## Repository

[https://github.com/eGroupAI/ai-sandbox-sdk-php](https://github.com/eGroupAI/ai-sandbox-sdk-php)
