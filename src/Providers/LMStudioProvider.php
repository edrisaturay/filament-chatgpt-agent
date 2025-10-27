<?php

namespace EdrisaTuray\FilamentAiChatAgent\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LMStudioProvider extends BaseAiProvider
{
    public function getId(): string
    {
        return 'lmstudio';
    }

    public function getName(): string
    {
        return 'LM Studio';
    }

    public function getDefaultModel(): string
    {
        return 'local-model';
    }

    public function getAvailableModels(): array
    {
        return [
            'local-model' => 'Local Model (Auto-detect)',
            'llama3.1' => 'Llama 3.1',
            'llama3' => 'Llama 3',
            'codellama' => 'Code Llama',
            'mistral' => 'Mistral',
            'mixtral' => 'Mixtral',
            'phi3' => 'Phi-3',
            'gemma' => 'Gemma',
            'qwen' => 'Qwen',
        ];
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['base_url']);
    }

    public function getConfigurationFields(): array
    {
        return [
            'base_url' => [
                'type' => 'url',
                'label' => 'LM Studio Base URL',
                'required' => true,
                'env_key' => 'LMSTUDIO_BASE_URL',
                'default' => 'http://localhost:1234',
                'placeholder' => 'http://localhost:1234',
            ],
            'api_key' => [
                'type' => 'password',
                'label' => 'API Key (Optional)',
                'required' => false,
                'env_key' => 'LMSTUDIO_API_KEY',
            ],
        ];
    }

    public function validateConfiguration(array $config): bool
    {
        return !empty($config['base_url']);
    }

    public function sendMessage(array $messages, array $config = []): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('LM Studio is not properly configured');
        }

        $baseUrl = rtrim($this->config['base_url'], '/');
        $url = "{$baseUrl}/v1/chat/completions";

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $this->temperature,
            'stream' => false,
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

        $headers = ['Content-Type' => 'application/json'];
        if (!empty($this->config['api_key'])) {
            $headers['Authorization'] = 'Bearer ' . $this->config['api_key'];
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(120) // LM Studio can be slower
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'content' => $data['choices'][0]['message']['content'] ?? '',
                    'usage' => $data['usage'] ?? null,
                ];
            } else {
                Log::error('LM Studio API Error', [
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
            Log::error('LM Studio Request Exception', [
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
