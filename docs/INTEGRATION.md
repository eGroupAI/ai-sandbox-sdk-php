# Integration Guide (PHP)

This SDK is designed for low-change, low-touch customer integration.

## Goals

- Stable API surface for v1.
- Explicit timeout and retry controls.
- Streaming chat support (`text/event-stream`) with `sendChatStream(...)`.

## Install

`composer require egroupai/ai-sandbox-sdk-php`

## First Steps

1. Configure `baseUrl` and `apiKey`.
2. Call `createAgent(...)`.
3. Create a chat channel with `createChatChannel(...)`.
4. Send a message with `sendChat(...)` or stream with `sendChatStream(...)`.

## SSE Streaming Example

```php
<?php

use EGroupAI\AiSandboxSdk\AiSandboxClient;

$client = new AiSandboxClient(
    baseUrl: "https://www.egroupai.com",
    apiKey: getenv("AI_SANDBOX_API_KEY") ?: ""
);

$chunks = $client->sendChatStream(123, [
    "channelId" => "abcd1234",
    "message" => "Please summarize my policy.",
    "stream" => true,
]);

foreach ($chunks as $chunk) {
    echo $chunk . PHP_EOL;
}
```

`sendChatStream(...)` returns a list of SSE `data:` chunks and stops automatically when `[DONE]` is received.
