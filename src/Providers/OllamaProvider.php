<?php

namespace EdrisaTuray\FilamentAiChatAgent\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaProvider extends BaseAiProvider
{
    public function getId(): string
    {
        return 'ollama';
    }

    public function getName(): string
    {
        return 'Ollama';
    }

    public function getDefaultModel(): string
    {
        return 'llama3.1';
    }

    public function getAvailableModels(): array
    {
        return [
            'llama3.1' => 'Llama 3.1',
            'llama3.1:8b' => 'Llama 3.1 8B',
            'llama3.1:70b' => 'Llama 3.1 70B',
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
                'label' => 'Ollama Base URL',
                'required' => true,
                'env_key' => 'OLLAMA_BASE_URL',
                'default' => 'http://localhost:11434',
                'placeholder' => 'http://localhost:11434',
            ],
            'api_key' => [
                'type' => 'password',
                'label' => 'API Key (Optional)',
                'required' => false,
                'env_key' => 'OLLAMA_API_KEY',
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
            throw new \Exception('Ollama is not properly configured');
        }

        $baseUrl = rtrim($this->config['base_url'], '/');
        $url = "{$baseUrl}/api/chat";

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => false,
            'options' => [
                'temperature' => $this->temperature,
            ],
        ];

        if ($this->maxTokens) {
            $payload['options']['num_predict'] = $this->maxTokens;
        }

        if ($this->systemMessage) {
            array_unshift($payload['messages'], [
                'role' => 'system',
                'content' => $this->systemMessage,
            ]);
        }

        $headers = ['Content-Type' => 'application/json'];
        if (!empty($this->config['api_key'])) {
            $headers['Authorization'] = 'Bearer ' . $this->config['api_key'];
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(120) // Ollama can be slower
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'content' => $data['message']['content'] ?? '',
                    'usage' => [
                        'prompt_tokens' => $data['prompt_eval_count'] ?? 0,
                        'completion_tokens' => $data['eval_count'] ?? 0,
                        'total_tokens' => ($data['prompt_eval_count'] ?? 0) + ($data['eval_count'] ?? 0),
                    ],
                ];
            } else {
                Log::error('Ollama API Error', [
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
            Log::error('Ollama Request Exception', [
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
