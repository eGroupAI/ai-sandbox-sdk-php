<?php

declare(strict_types=1);

namespace EGroupAI\AiSandboxSdk;

final class AiSandboxClient
{
    public function __construct(
        private string $baseUrl,
        private string $apiKey,
        private int $timeoutSeconds = 30,
        private int $maxRetries = 2
    ) {
        $this->baseUrl = rtrim($baseUrl, "/");
    }

    private function request(string $method, string $path, ?array $payload = null, string $accept = "application/json"): string
    {
        $attempt = 0;
        while (true) {
            $traceId = null;
            $ch = curl_init("{$this->baseUrl}/api/v1{$path}");
            $headers = [
                "Authorization: Bearer {$this->apiKey}",
                "Accept: {$accept}",
            ];
            if ($payload !== null) {
                $headers[] = "Content-Type: application/json";
            }

            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $this->timeoutSeconds,
                CURLOPT_POSTFIELDS => $payload !== null ? json_encode($payload, JSON_UNESCAPED_UNICODE) : null,
                CURLOPT_HEADERFUNCTION => static function ($curl, string $headerLine) use (&$traceId): int {
                    if (stripos($headerLine, "x-trace-id:") === 0) {
                        $traceId = trim(substr($headerLine, strlen("x-trace-id:")));
                    }
                    return strlen($headerLine);
                },
            ]);
            $raw = curl_exec($ch);
            if ($raw === false) {
                $error = curl_error($ch);
                curl_close($ch);
                if ($attempt < $this->maxRetries) {
                    $attempt++;
                    usleep(200000 * $attempt);
                    continue;
                }
                throw new \RuntimeException("Network error: {$error}");
            }
            $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            if (HttpRetryPolicy::shouldRetryTransientHttpStatus($method, $status) && $attempt < $this->maxRetries) {
                $attempt++;
                usleep(200000 * $attempt);
                continue;
            }
            if ($status >= 400) {
                throw new ApiException($status, $raw, $traceId);
            }
            return $raw;
        }
    }

    private function json(string $method, string $path, ?array $payload = null): array
    {
        return json_decode($this->request($method, $path, $payload), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @return list<string>
     */
    private function parseSseData(string $raw): array
    {
        $chunks = [];
        $lines = preg_split("/\r\n|\r|\n/", $raw) ?: [];
        foreach ($lines as $line) {
            if (!str_starts_with($line, "data: ")) {
                continue;
            }
            $data = trim(substr($line, 6));
            if ($data === "[DONE]") {
                break;
            }
            if ($data !== "") {
                $chunks[] = $data;
            }
        }
        return $chunks;
    }

    public function createAgent(array $payload): array { return $this->json("POST", "/agents", $payload); }
    public function updateAgent(int $agentId, array $payload): array { return $this->json("PUT", "/agents/{$agentId}", $payload); }
    public function listAgents(string $query = ""): array { return $this->json("GET", "/agents" . ($query === "" ? "" : "?{$query}")); }
    public function getAgentDetail(int $agentId): array { return $this->json("GET", "/agents/{$agentId}"); }
    public function createChatChannel(int $agentId, array $payload): array { return $this->json("POST", "/agents/{$agentId}/channels", $payload); }
    public function sendChat(int $agentId, array $payload): array { return $this->json("POST", "/agents/{$agentId}/chat", $payload); }
    /**
     * @return list<string>
     */
    public function sendChatStream(int $agentId, array $payload): array
    {
        $raw = $this->request("POST", "/agents/{$agentId}/chat", $payload, "text/event-stream");
        return $this->parseSseData($raw);
    }
    public function getChatHistory(int $agentId, string $channelId, string $query = "limit=50&page=0"): array { return $this->json("GET", "/agents/{$agentId}/channels/{$channelId}/messages?{$query}"); }
    public function getKnowledgeBaseArticles(int $agentId, int $collectionId, string $query = "startIndex=0"): array { return $this->json("GET", "/agents/{$agentId}/collections/{$collectionId}/articles?{$query}"); }
    public function createKnowledgeBase(int $agentId, array $payload): array { return $this->json("POST", "/agents/{$agentId}/collections", $payload); }
    public function updateKnowledgeBaseStatus(int $agentCollectionId, array $payload): array { return $this->json("PATCH", "/agent-collections/{$agentCollectionId}/status", $payload); }
    public function listKnowledgeBases(int $agentId, string $query = "activeOnly=false"): array { return $this->json("GET", "/agents/{$agentId}/collections?{$query}"); }
}
