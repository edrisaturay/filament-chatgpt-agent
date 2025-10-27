<?php

namespace EdrisaTuray\FilamentAiChatAgent\Providers;

class ProviderConfigHelper
{
    public static function getAzureOpenAIConfig(): array
    {
        return [
            'api_key' => env('AZURE_OPENAI_API_KEY'),
            'endpoint' => env('AZURE_OPENAI_ENDPOINT'),
            'deployment_name' => env('AZURE_OPENAI_DEPLOYMENT_NAME'),
            'api_version' => env('AZURE_OPENAI_API_VERSION', '2024-02-15-preview'),
        ];
    }

    public static function getOllamaConfig(): array
    {
        return [
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'api_key' => env('OLLAMA_API_KEY'),
        ];
    }

    public static function getLMStudioConfig(): array
    {
        return [
            'base_url' => env('LMSTUDIO_BASE_URL', 'http://localhost:1234'),
            'api_key' => env('LMSTUDIO_API_KEY'),
        ];
    }

    public static function getCustomEndpointConfig(): array
    {
        return [
            'endpoint_url' => env('CUSTOM_AI_ENDPOINT_URL'),
            'api_key' => env('CUSTOM_AI_API_KEY'),
            'auth_type' => env('CUSTOM_AI_AUTH_TYPE', 'bearer'),
            'model_field' => env('CUSTOM_AI_MODEL_FIELD', 'model'),
            'messages_field' => env('CUSTOM_AI_MESSAGES_FIELD', 'messages'),
            'temperature_field' => env('CUSTOM_AI_TEMPERATURE_FIELD', 'temperature'),
            'max_tokens_field' => env('CUSTOM_AI_MAX_TOKENS_FIELD', 'max_tokens'),
            'response_path' => env('CUSTOM_AI_RESPONSE_PATH', 'choices.0.message.content'),
        ];
    }

    public static function getConfigForProvider(string $provider): array
    {
        return match ($provider) {
            'chatgpt' => self::getChatGPTConfig(),
            'azure-openai' => self::getAzureOpenAIConfig(),
            'ollama' => self::getOllamaConfig(),
            'lmstudio' => self::getLMStudioConfig(),
            'custom-endpoint' => self::getCustomEndpointConfig(),
            default => [],
        };
    }

    public static function getActiveProvider(): string
    {
        return env('FILAMENT_AI_CHAT_PROVIDER', 'chatgpt');
    }

    public static function getActiveProviderConfig(): array
    {
        $provider = self::getActiveProvider();
        return self::getConfigForProvider($provider);
    }
}
