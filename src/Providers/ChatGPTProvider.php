<?php

namespace EdrisaTuray\FilamentAiChatAgent\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatGPTProvider extends BaseAiProvider
{
    /**
     * Get the unique identifier for this provider.
     * 
     * @return string
     */
    public function getId(): string
    {
        return 'chatgpt';
    }

    /**
     * Get the display name for this provider.
     * 
     * @return string
     */
    public function getName(): string
    {
        return 'ChatGPT (OpenAI)';
    }

    public function getDefaultModel(): string
    {
        return 'gpt-4o-mini';
    }

    public function getAvailableModels(): array
    {
        return [
            'gpt-4o' => 'GPT-4 Omni',
            'gpt-4o-mini' => 'GPT-4 Omni Mini',
            'gpt-4-turbo' => 'GPT-4 Turbo',
            'gpt-4' => 'GPT-4',
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
        ];
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['api_key']);
    }

    public function getConfigurationFields(): array
    {
        return [
            'api_key' => [
                'type' => 'password',
                'label' => 'OpenAI API Key',
                'required' => true,
                'env_key' => 'OPENAI_API_KEY',
            ],
            'organization' => [
                'type' => 'text',
                'label' => 'Organization ID (Optional)',
                'required' => false,
                'env_key' => 'OPENAI_ORGANIZATION',
            ],
            'base_url' => [
                'type' => 'url',
                'label' => 'Base URL (Optional)',
                'required' => false,
                'env_key' => 'OPENAI_BASE_URL',
                'default' => 'https://api.openai.com/v1',
                'placeholder' => 'https://api.openai.com/v1',
            ],
        ];
    }

    public function validateConfiguration(array $config): bool
    {
        return !empty($config['api_key']);
    }

    public function sendMessage(array $messages, array $config = []): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('ChatGPT is not properly configured');
        }

        $apiKey = $this->config['api_key'];
        $baseUrl = $this->config['base_url'] ?? 'https://api.openai.com/v1';
        $organization = $this->config['organization'] ?? null;

        $url = rtrim($baseUrl, '/') . '/chat/completions';

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $this->temperature,
        ];

        if ($this->maxTokens) {
            $payload['max_tokens'] = $this->maxTokens;
        }

        if ($this->systemMessage) {
            array_unshift($payload['messages'], [
                'role' => 'system',
                'content' => $this->systemMessage,
            ]);
        }

        if (!empty($this->functions)) {
            $payload['functions'] = $this->functions;
        }

        $headers = [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ];

        if ($organization) {
            $headers['OpenAI-Organization'] = $organization;
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(60)
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'content' => $data['choices'][0]['message']['content'] ?? '',
                    'usage' => $data['usage'] ?? null,
                ];
            } else {
                Log::error('ChatGPT API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                
                return [
                    'success' => false,
                    'error' => 'API request failed: ' . $response->status(),
                    'content' => 'Sorry, I encountered an error. Please try again.',
                ];
            }
        } catch (\Exception $e) {
            Log::error('ChatGPT Request Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'content' => 'Sorry, I encountered an error. Please try again.',
            ];
        }
    }
}
