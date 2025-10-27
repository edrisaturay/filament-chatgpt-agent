<?php

namespace EdrisaTuray\FilamentAiChatAgent\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomEndpointProvider extends BaseAiProvider
{
    public function getId(): string
    {
        return 'custom-endpoint';
    }

    public function getName(): string
    {
        return 'Custom Endpoint';
    }

    public function getDefaultModel(): string
    {
        return 'custom-model';
    }

    public function getAvailableModels(): array
    {
        return [
            'custom-model' => 'Custom Model',
            'gpt-4' => 'GPT-4 Compatible',
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo Compatible',
            'claude-3' => 'Claude 3 Compatible',
            'llama3' => 'Llama 3 Compatible',
        ];
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['endpoint_url']);
    }

    public function getConfigurationFields(): array
    {
        return [
            'endpoint_url' => [
                'type' => 'url',
                'label' => 'Endpoint URL',
                'required' => true,
                'env_key' => 'CUSTOM_AI_ENDPOINT_URL',
                'placeholder' => 'https://your-custom-endpoint.com/v1/chat/completions',
            ],
            'api_key' => [
                'type' => 'password',
                'label' => 'API Key',
                'required' => false,
                'env_key' => 'CUSTOM_AI_API_KEY',
            ],
            'auth_type' => [
                'type' => 'select',
                'label' => 'Authentication Type',
                'required' => false,
                'options' => [
                    'bearer' => 'Bearer Token',
                    'api-key' => 'API Key Header',
                    'basic' => 'Basic Auth',
                    'none' => 'No Authentication',
                ],
                'default' => 'bearer',
            ],
            'model_field' => [
                'type' => 'text',
                'label' => 'Model Field Name',
                'required' => false,
                'default' => 'model',
                'placeholder' => 'model',
            ],
            'messages_field' => [
                'type' => 'text',
                'label' => 'Messages Field Name',
                'required' => false,
                'default' => 'messages',
                'placeholder' => 'messages',
            ],
            'temperature_field' => [
                'type' => 'text',
                'label' => 'Temperature Field Name',
                'required' => false,
                'default' => 'temperature',
                'placeholder' => 'temperature',
            ],
            'max_tokens_field' => [
                'type' => 'text',
                'label' => 'Max Tokens Field Name',
                'required' => false,
                'default' => 'max_tokens',
                'placeholder' => 'max_tokens',
            ],
            'response_path' => [
                'type' => 'text',
                'label' => 'Response Content Path',
                'required' => false,
                'default' => 'choices.0.message.content',
                'placeholder' => 'choices.0.message.content',
            ],
        ];
    }

    public function validateConfiguration(array $config): bool
    {
        return !empty($config['endpoint_url']);
    }

    public function sendMessage(array $messages, array $config = []): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Custom Endpoint is not properly configured');
        }

        $endpointUrl = $this->config['endpoint_url'];
        $modelField = $this->config['model_field'] ?? 'model';
        $messagesField = $this->config['messages_field'] ?? 'messages';
        $temperatureField = $this->config['temperature_field'] ?? 'temperature';
        $maxTokensField = $this->config['max_tokens_field'] ?? 'max_tokens';
        $responsePath = $this->config['response_path'] ?? 'choices.0.message.content';

        $payload = [
            $modelField => $this->model,
            $messagesField => $messages,
            $temperatureField => $this->temperature,
        ];

        if ($this->maxTokens) {
            $payload[$maxTokensField] = $this->maxTokens;
        }

        if ($this->systemMessage) {
            array_unshift($payload[$messagesField], [
                'role' => 'system',
                'content' => $this->systemMessage,
            ]);
        }

        if (!empty($this->functions)) {
            $payload['functions'] = $this->functions;
        }

        $headers = ['Content-Type' => 'application/json'];
        
        // Handle authentication
        if (!empty($this->config['api_key'])) {
            $authType = $this->config['auth_type'] ?? 'bearer';
            
            switch ($authType) {
                case 'bearer':
                    $headers['Authorization'] = 'Bearer ' . $this->config['api_key'];
                    break;
                case 'api-key':
                    $headers['X-API-Key'] = $this->config['api_key'];
                    break;
                case 'basic':
                    $headers['Authorization'] = 'Basic ' . base64_encode($this->config['api_key']);
                    break;
            }
        }

        try {
            $response = Http::withHeaders($headers)
                ->timeout(60)
                ->post($endpointUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Extract content using the configured path
                $content = $this->getNestedValue($data, $responsePath);
                
                return [
                    'success' => true,
                    'content' => $content ?? '',
                    'usage' => $data['usage'] ?? null,
                ];
            } else {
                Log::error('Custom Endpoint API Error', [
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
            Log::error('Custom Endpoint Request Exception', [
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

    private function getNestedValue(array $data, string $path): mixed
    {
        $keys = explode('.', $path);
        $value = $data;

        foreach ($keys as $key) {
            if (is_array($value) && array_key_exists($key, $value)) {
                $value = $value[$key];
            } else {
                return null;
            }
        }

        return $value;
    }
}
