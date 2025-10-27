<?php

namespace EdrisaTuray\FilamentAiChatAgent\Providers;

interface AiProviderInterface
{
    public function getId(): string;
    public function getName(): string;
    public function getIcon(): string;
    public function getDefaultModel(): string;
    public function getAvailableModels(): array;
    public function isConfigured(): bool;
    public function getConfigurationFields(): array;
    public function validateConfiguration(array $config): bool;
    public function sendMessage(array $messages, array $config = []): array;
}
