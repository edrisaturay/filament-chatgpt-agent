<?php

// Example configuration for different AI providers
// Copy this to your Filament panel configuration

use EdrisaTuray\FilamentAiChatAgent\AIChatAgentPlugin;
use EdrisaTuray\FilamentAiChatAgent\Providers\ProviderConfigHelper;

return [
    // ChatGPT Configuration (Default)
    'chatgpt' => AIChatAgentPlugin::make()
        ->provider('chatgpt')
        ->botName('ChatGPT Assistant')
        ->model(env('FILAMENT_AI_CHAT_MODEL', 'gpt-4o'))
        ->temperature(env('FILAMENT_AI_CHAT_TEMPERATURE', 0.7))
        ->maxTokens(env('FILAMENT_AI_CHAT_MAX_TOKENS', 2000))
        ->providerConfig(ProviderConfigHelper::getChatGPTConfig())
        ->systemMessage('You are a helpful AI assistant powered by OpenAI.')
        ->startMessage('Hello! I\'m your ChatGPT assistant. How can I help you today?'),

    // Azure OpenAI Configuration
    'azure' => AIChatAgentPlugin::make()
        ->provider('azure')
        ->botName('Azure AI Assistant')
        ->model(env('FILAMENT_AI_CHAT_MODEL', 'gpt-4o'))
        ->temperature(env('FILAMENT_AI_CHAT_TEMPERATURE', 0.7))
        ->maxTokens(env('FILAMENT_AI_CHAT_MAX_TOKENS', 2000))
        ->providerConfig(ProviderConfigHelper::getAzureOpenAIConfig())
        ->systemMessage('You are a helpful AI assistant powered by Azure OpenAI.')
        ->startMessage('Hello! I\'m your Azure AI assistant. How can I help you today?'),

    // Ollama Configuration (Local AI)
    'ollama' => AIChatAgentPlugin::make()
        ->provider('ollama')
        ->botName('Local AI Assistant')
        ->model(env('FILAMENT_AI_CHAT_MODEL', 'llama3.1'))
        ->temperature(env('FILAMENT_AI_CHAT_TEMPERATURE', 0.7))
        ->maxTokens(env('FILAMENT_AI_CHAT_MAX_TOKENS', 2000))
        ->providerConfig(ProviderConfigHelper::getOllamaConfig())
        ->systemMessage('You are a helpful AI assistant running locally.')
        ->startMessage('Hello! I\'m your local AI assistant. How can I help you today?'),

    // LM Studio Configuration
    'lmstudio' => AIChatAgentPlugin::make()
        ->provider('lmstudio')
        ->botName('LM Studio Assistant')
        ->model(env('FILAMENT_AI_CHAT_MODEL', 'local-model'))
        ->temperature(env('FILAMENT_AI_CHAT_TEMPERATURE', 0.7))
        ->maxTokens(env('FILAMENT_AI_CHAT_MAX_TOKENS', 2000))
        ->providerConfig(ProviderConfigHelper::getLMStudioConfig())
        ->systemMessage('You are a helpful AI assistant running on LM Studio.')
        ->startMessage('Hello! I\'m your LM Studio AI assistant. How can I help you today?'),

    // Custom Endpoint Configuration
    'custom-endpoint' => AIChatAgentPlugin::make()
        ->provider('custom-endpoint')
        ->botName('Custom AI Assistant')
        ->model(env('FILAMENT_AI_CHAT_MODEL', 'custom-model'))
        ->temperature(env('FILAMENT_AI_CHAT_TEMPERATURE', 0.7))
        ->maxTokens(env('FILAMENT_AI_CHAT_MAX_TOKENS', 2000))
        ->providerConfig(ProviderConfigHelper::getCustomEndpointConfig())
        ->systemMessage('You are a helpful AI assistant.')
        ->startMessage('Hello! I\'m your custom AI assistant. How can I help you today?'),
];
