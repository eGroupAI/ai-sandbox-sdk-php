<?php
require __DIR__ . '/../vendor/autoload.php';

use EGroupAI\AiSandboxSdk\AiSandboxClient;

$client = new AiSandboxClient(
    getenv('AI_SANDBOX_BASE_URL') ?: 'https://www.egroupai.com',
    getenv('AI_SANDBOX_API_KEY') ?: ''
);

$agent = $client->createAgent([
    'agentDisplayName' => 'PHP SDK Quickstart',
    'agentDescription' => 'Created by PHP SDK',
]);
print_r($agent);
