<?php

namespace EdrisaTuray\FilamentAiChatAgent\Providers;

use EdrisaTuray\FilamentAiChatAgent\Providers\ChatGPTProvider;
use EdrisaTuray\FilamentAiChatAgent\Providers\AzureOpenAIProvider;
use EdrisaTuray\FilamentAiChatAgent\Providers\OllamaProvider;
use EdrisaTuray\FilamentAiChatAgent\Providers\LMStudioProvider;
use EdrisaTuray\FilamentAiChatAgent\Providers\CustomEndpointProvider;

class ProviderManager
{
    /**
     * Array of registered AI providers.
     * 
     * @var array<string, AiProviderInterface>
     */
    protected array $providers = [];
    
    /**
     * The default provider ID.
     * 
     * @var string
     */
    protected string $defaultProvider = 'chatgpt';

    /**
     * Initialize the provider manager and register default providers.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->registerDefaultProviders();
    }

    protected function registerDefaultProviders(): void
    {
        $this->providers = [
            'chatgpt' => ChatGPTProvider::class,
            'azure' => AzureOpenAIProvider::class,
            'ollama' => OllamaProvider::class,
            'lmstudio' => LMStudioProvider::class,
            'custom-endpoint' => CustomEndpointProvider::class,
        ];
    }

    public function registerProvider(string $id, string $providerClass): void
    {
        $this->providers[$id] = $providerClass;
    }

    public function getProvider(string $id, array $config = []): AiProviderInterface
    {
        if (!isset($this->providers[$id])) {
            throw new \Exception("Provider '{$id}' not found");
        }

        $providerClass = $this->providers[$id];
        return new $providerClass($config);
    }

    public function getAvailableProviders(): array
    {
        return array_keys($this->providers);
    }

    public function getProviderInfo(string $id): array
    {
        $provider = $this->getProvider($id);
        
        return [
            'id' => $provider->getId(),
            'name' => $provider->getName(),
            'icon' => $provider->getIcon(),
            'default_model' => $provider->getDefaultModel(),
            'available_models' => $provider->getAvailableModels(),
            'configuration_fields' => $provider->getConfigurationFields(),
            'is_configured' => $provider->isConfigured(),
        ];
    }

    public function getAllProvidersInfo(): array
    {
        $info = [];
        
        foreach ($this->providers as $id => $providerClass) {
            try {
                $provider = $this->getProvider($id);
                $info[$id] = $this->getProviderInfo($id);
            } catch (\Exception $e) {
                $info[$id] = [
                    'id' => $id,
                    'name' => 'Unknown Provider',
                    'icon' => 'ai-chat-agent::components.custom-endpoint.svg',
                    'default_model' => 'unknown',
                    'available_models' => [],
                    'configuration_fields' => [],
                    'is_configured' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        return $info;
    }

    public function getDefaultProvider(): string
    {
        return $this->defaultProvider;
    }

    public function setDefaultProvider(string $providerId): void
    {
        if (!isset($this->providers[$providerId])) {
            throw new \Exception("Provider '{$providerId}' not found");
        }
        
        $this->defaultProvider = $providerId;
    }

    public function getActiveProvider(array $config = []): AiProviderInterface
    {
        $providerId = $config['provider'] ?? $this->defaultProvider;
        return $this->getProvider($providerId, $config);
    }
}
