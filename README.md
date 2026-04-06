# AI Sandbox SDK for PHP

![Motion headline](https://readme-typing-svg.demolab.com?font=Inter&weight=700&size=24&duration=2800&pause=800&color=777BB4&background=FFFFFF00&width=900&lines=Elegant+AI+Integration+for+PHP+Teams;11+APIs+%7C+SSE+Streaming+%7C+GA+v1)

![GA](https://img.shields.io/badge/GA-v1-0A84FF?style=for-the-badge)
![APIs](https://img.shields.io/badge/APIs-11-00A86B?style=for-the-badge)
![Streaming](https://img.shields.io/badge/SSE-Ready-7C3AED?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-SDK-777BB4?style=for-the-badge)

## UX-First Value Cards

| Quick Integration | Real-Time Experience | Reliability by Default |
| --- | --- | --- |
| Simple API surface with minimal integration friction | `sendChatStream(...)` for SSE chunk handling | Timeout + retry controls for production reliability |

## Visual Integration Flow

```mermaid
flowchart LR
  A[Create Agent] --> B[Create Chat Channel]
  B --> C[Send Message]
  C --> D[SSE Stream Chunks]
  D --> E[Attach Knowledge Base]
  E --> F[Customer-Ready Experience]
```

## 60-Second Quick Start

```php
<?php

use EGroupAI\AiSandboxSdk\AiSandboxClient;

$client = new AiSandboxClient(
    baseUrl: getenv("AI_SANDBOX_BASE_URL") ?: "https://www.egroupai.com",
    apiKey: getenv("AI_SANDBOX_API_KEY") ?: ""
);

$agent = $client->createAgent([
    "agentDisplayName" => "Support Agent",
    "agentDescription" => "Handles customer inquiries",
]);
$agentId = (int)($agent["payload"]["agentId"] ?? 0);

$channel = $client->createChatChannel($agentId, [
    "title" => "Web Chat",
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

## Installation

```bash
composer require egroupai/ai-sandbox-sdk-php
```

## Snapshot

| Metric | Value |
| --- | --- |
| API Coverage | 11 operations (Agent / Chat / Knowledge Base) |
| Stream Mode | `text/event-stream` with `[DONE]` handling |
| Error Surface | `ApiException` with statusCode/responseBody |
| Validation | Production-host integration verified |

## Links

- [Official System Integration Docs](https://www.egroupai.com/ai-sandbox/system-integration)
- [30-Day Optimization Plan](docs/30D_OPTIMIZATION_PLAN.md)
- [Integration Guide](docs/INTEGRATION.md)
- [Quickstart Example](examples/quickstart.php)
- [Repository](https://github.com/eGroupAI/ai-sandbox-sdk-php)
