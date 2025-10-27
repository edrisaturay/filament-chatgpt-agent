<?php

namespace EdrisaTuray\FilamentAiChatAgent\Providers;

/**
 * Base AI Provider
 * 
 * Abstract base class that provides common functionality for all AI providers.
 * Implements shared logic for model configuration, temperature, tokens, and system messages.
 * 
 * @package EdrisaTuray\FilamentAiChatAgent\Providers
 * @author Edrisa A Turay <edrisa@edrisa.com>
 * @since 1.0.0
 */
abstract class BaseAiProvider implements AiProviderInterface
{
    /**
     * Provider configuration array.
     * 
     * @var array
     */
    protected array $config = [];
    
    /**
     * The AI model to use for requests.
     * 
     * @var string
     */
    protected string $model = '';
    
    /**
     * Temperature setting for response randomness (0.0 to 1.0).
     * 
     * @var float
     */
    protected float $temperature = 0.7;
    
    /**
     * Maximum number of tokens to generate.
     * 
     * @var int|null
     */
    protected ?int $maxTokens = null;
    
    /**
     * System message to set the AI's behavior.
     * 
     * @var string
     */
    protected string $systemMessage = '';
    
    /**
     * Array of function definitions for function calling.
     * 
     * @var array
     */
    protected array $functions = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->model = $config['model'] ?? $this->getDefaultModel();
        $this->temperature = $config['temperature'] ?? 0.7;
        $this->maxTokens = $config['max_tokens'] ?? null;
        $this->systemMessage = $config['system_message'] ?? '';
        $this->functions = $config['functions'] ?? [];
    }

    public function setModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setTemperature(float $temperature): static
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function setMaxTokens(?int $maxTokens): static
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }

    public function getMaxTokens(): ?int
    {
        return $this->maxTokens;
    }

    public function setSystemMessage(string $message): static
    {
        $this->systemMessage = $message;
        return $this;
    }

    public function getSystemMessage(): string
    {
        return $this->systemMessage;
    }

    public function setFunctions(array $functions): static
    {
        $this->functions = $functions;
        return $this;
    }

    public function getFunctions(): array
    {
        return $this->functions;
    }

    public function getIcon(): string
    {
        return "ai-chat-agent::{$this->getId()}-svg";
    }

    abstract public function getId(): string;
    abstract public function getName(): string;
    abstract public function getDefaultModel(): string;
    abstract public function getAvailableModels(): array;
    abstract public function isConfigured(): bool;
    abstract public function getConfigurationFields(): array;
    abstract public function validateConfiguration(array $config): bool;
    abstract public function sendMessage(array $messages, array $config = []): array;
}
