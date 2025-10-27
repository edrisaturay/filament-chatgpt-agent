<?php

namespace EdrisaTuray\FilamentAiChatAgent\Providers;

abstract class BaseAiProvider implements AiProviderInterface
{
    protected array $config = [];
    protected string $model = '';
    protected float $temperature = 0.7;
    protected ?int $maxTokens = null;
    protected string $systemMessage = '';
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
        return "ai-chat-agent::components.{$this->getId()}.{$this->getId()}-svg";
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
