<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Chat Agent Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the default configuration for the AI Chat Agent package.
    | You can override these settings by publishing this config file.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | The default AI provider to use for chat functionality.
    | Supported providers: chatgpt, azure, ollama, lmstudio, custom-endpoint
    |
    */
    'default_provider' => env('FILAMENT_AI_CHAT_PROVIDER', 'chatgpt'),

    /*
    |--------------------------------------------------------------------------
    | AI Model Configuration
    |--------------------------------------------------------------------------
    |
    | Default model settings for AI providers.
    |
    */
    'model' => [
        'default' => env('FILAMENT_AI_CHAT_MODEL', 'gpt-4o-mini'),
        'temperature' => env('FILAMENT_AI_CHAT_TEMPERATURE', 0.7),
        'max_tokens' => env('FILAMENT_AI_CHAT_MAX_TOKENS', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider Configurations
    |--------------------------------------------------------------------------
    |
    | Configuration for each AI provider.
    |
    */
    'providers' => [
        'chatgpt' => [
            'api_key' => env('OPENAI_API_KEY'),
            'organization' => env('OPENAI_ORGANIZATION'),
        ],
        'azure' => [
            'api_key' => env('AZURE_OPENAI_API_KEY'),
            'endpoint' => env('AZURE_OPENAI_ENDPOINT'),
            'deployment_name' => env('AZURE_OPENAI_DEPLOYMENT_NAME'),
            'api_version' => env('AZURE_OPENAI_API_VERSION', '2024-02-15-preview'),
        ],
        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'api_key' => env('OLLAMA_API_KEY'),
        ],
        'lmstudio' => [
            'base_url' => env('LMSTUDIO_BASE_URL', 'http://localhost:1234'),
            'api_key' => env('LMSTUDIO_API_KEY'),
        ],
        'custom-endpoint' => [
            'base_url' => env('CUSTOM_ENDPOINT_URL'),
            'api_key' => env('CUSTOM_ENDPOINT_API_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    |
    | Default UI settings for the chat interface.
    |
    */
    'ui' => [
        'bot_name' => env('FILAMENT_AI_CHAT_BOT_NAME', 'AI Assistant'),
        'button_text' => env('FILAMENT_AI_CHAT_BUTTON_TEXT', 'Ask AI'),
        'button_icon' => env('FILAMENT_AI_CHAT_BUTTON_ICON', 'heroicon-m-sparkles'),
        'sending_text' => env('FILAMENT_AI_CHAT_SENDING_TEXT', 'Sending...'),
        'default_panel_width' => env('FILAMENT_AI_CHAT_PANEL_WIDTH', '350px'),
        'start_message' => env('FILAMENT_AI_CHAT_START_MESSAGE', false),
        'logo_url' => env('FILAMENT_AI_CHAT_LOGO_URL', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Page Watcher Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the page watcher feature that monitors page content.
    |
    */
    'page_watcher' => [
        'enabled' => env('FILAMENT_AI_CHAT_PAGE_WATCHER_ENABLED', false),
        'selector' => env('FILAMENT_AI_CHAT_PAGE_WATCHER_SELECTOR', '.fi-page'),
        'message' => env('FILAMENT_AI_CHAT_PAGE_WATCHER_MESSAGE', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | System Message
    |--------------------------------------------------------------------------
    |
    | Default system message for AI providers.
    |
    */
    'system_message' => env('FILAMENT_AI_CHAT_SYSTEM_MESSAGE', ''),

    /*
    |--------------------------------------------------------------------------
    | Functions
    |--------------------------------------------------------------------------
    |
    | Default functions available to AI providers.
    |
    */
    'functions' => [],
];
