<?php

namespace EdrisaTuray\FilamentAiChatAgent;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Closure;
use EdrisaTuray\FilamentAiChatAgent\Providers\ProviderManager;

/**
 * AI Chat Agent Plugin for Filament
 * 
 * This plugin provides a multi-provider AI chat interface for Filament applications.
 * It supports various AI providers including ChatGPT, Azure OpenAI, Ollama, LM Studio, and custom endpoints.
 * 
 * @package EdrisaTuray\FilamentAiChatAgent
 * @author Edrisa A Turay <edrisa@edrisa.com>
 * @since 1.0.0
 */
class AIChatAgentPlugin implements Plugin
{
    protected bool|Closure|null $enabled = null;
    protected string|Closure|null $botName = null;
    protected string|Closure|null $buttonText = null;
    protected string|Closure $buttonIcon = 'heroicon-m-sparkles';
    protected string|Closure|null $sendingText = null;
    protected string|Closure $model;
    protected float|Closure|null $temperature;
    protected int|Closure|null $maxTokens;
    protected string|Closure $systemMessage = '';
    protected array|Closure $functions = [];
    protected bool|Closure|null $pageWatcherEnabled = false;
    protected string|Closure $pageWatcherSelector = '.fi-page';
    protected string|Closure|null $pageWatcherMessage = null;
    protected string|Closure $defaultPanelWidth = '350px';
    protected bool|string|Closure|null $startMessage = false;
    protected bool|string|Closure|null $logoUrl = false;
    protected string|Closure $provider = 'chatgpt';
    protected array|Closure $providerConfig = [];

    /**
     * Initialize the plugin with environment variable defaults.
     */
    public function __construct()
    {
        $this->model = env('FILAMENT_AI_CHAT_MODEL', 'gpt-4o-mini');
        
        $temperature = env('FILAMENT_AI_CHAT_TEMPERATURE');
        $this->temperature = $temperature !== null ? (float) $temperature : 0.7;
        
        $maxTokens = env('FILAMENT_AI_CHAT_MAX_TOKENS');
        $this->maxTokens = $maxTokens !== null ? (int) $maxTokens : null;
    }

    /**
     * Create a new instance of the plugin.
     * 
     * @return static
     */
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * Get the unique identifier for this plugin.
     * 
     * @return string
     */
    public function getId(): string
    {
        return 'ai-chat-agent';
    }

    /**
     * Register the plugin with the Filament panel.
     * 
     * @param Panel $panel
     * @return void
     */
    public function register(Panel $panel): void
    {
        $panel
            ->renderHook(
                'panels::body.end',
                fn () => view('ai-chat-agent::components.filament-ai-chat-agent'),
            );
    }

    public function boot(Panel $panel): void
    {
        //
    }

    /**
     * Set the AI provider to use for chat functionality.
     * 
     * @param string|Closure $provider The provider ID (chatgpt, azure, ollama, lmstudio, custom-endpoint)
     * @return static
     */
    public function provider(string|Closure $provider): static
    {
        $this->provider = $provider;

        return $this;
    }

    public function getProvider(): string
    {
        if (is_callable($this->provider)) {
            return ($this->provider)();
        }

        return $this->provider;
    }

    public function providerConfig(array|Closure $config): static
    {
        $this->providerConfig = $config;

        return $this;
    }

    public function getProviderConfig(): array
    {
        if (is_callable($this->providerConfig)) {
            return ($this->providerConfig)();
        }

        return $this->providerConfig;
    }

    public function getProviderManager(): ProviderManager
    {
        return app(ProviderManager::class);
    }

    public function enabled(bool|Closure $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isEnabled(): bool
    {
        if (is_null($this->enabled)){
            return auth()->check();
        }
        return is_callable($this->enabled) ? ($this->enabled)() : $this->enabled;
    }

    public function botName(string|Closure $name): static
    {
        $this->botName = $name;

        return $this;
    }

    public function getBotName(): string
    {
        if (is_callable($this->botName)) {
            return ($this->botName)();
        }

        return $this->botName ?? __('ai-chat-agent::translations.bot_name');
    }

    public function buttonText(string|Closure $text): static
    {
        $this->buttonText = $text;

        return $this;
    }

    public function getButtonText(): string
    {
        if (is_callable($this->buttonText)) {
            return ($this->buttonText)();
        }

        return $this->buttonText ?? __('ai-chat-agent::translations.button_text');
    }

    public function buttonIcon(string|Closure $icon): static
    {
        $this->buttonIcon = $icon;

        return $this;
    }

    public function getButtonIcon(): string
    {
        if (is_callable($this->buttonIcon)) {
            return ($this->buttonIcon)();
        }

        return $this->buttonIcon;
    }

    public function sendingText(string|Closure $text): static
    {
        $this->sendingText = $text;

        return $this;
    }

    public function getSendingText(): string
    {
        if (is_callable($this->sendingText)) {
            return ($this->sendingText)();
        }

        return $this->sendingText ??__('ai-chat-agent::translations.sending_text');
    }

    public function model(string|Closure $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getModel(): string
    {
        if (is_callable($this->model)) {
            return ($this->model)();
        }

        return $this->model;
    }

    public function temperature(float|Closure $temperature): static
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getTemperature(): ?float
    {
        if (is_callable($this->temperature)) {
            return ($this->temperature)();
        }

        return $this->temperature;
    }

    public function maxTokens(int|Closure $maxTokens): static
    {
        $this->maxTokens = $maxTokens;

        return $this;
    }

    public function getMaxTokens(): ?int
    {
        if (is_callable($this->maxTokens)) {
            return ($this->maxTokens)();
        }

        return $this->maxTokens;
    }

    public function systemMessage(string|Closure $message): static
    {
        $this->systemMessage = $message;

        return $this;
    }

    public function getSystemMessage(): string
    {
        if (is_callable($this->systemMessage)) {
            return ($this->systemMessage)();
        }

        return $this->systemMessage;
    }

    public function functions(array|Closure $functions): static
    {
        $this->functions = $functions;

        return $this;
    }

    public function getFunctions(): array
    {
        return $this->functions;
    }

    public function defaultPanelWidth(string|Closure $width): static
    {
        $this->defaultPanelWidth = $width;

        return $this;
    }

    public function getDefaultPanelWidth(): string
    {
        if (is_callable($this->defaultPanelWidth)) {
            return ($this->defaultPanelWidth)();
        }

        return $this->defaultPanelWidth;
    }

    public function pageWatcherEnabled(bool|Closure $enabled): static
    {
        $this->pageWatcherEnabled = $enabled;

        return $this;
    }

    public function isPageWatcherEnabled(): bool
    {
        if (is_null($this->pageWatcherEnabled)){
            return false;
        }

        return is_callable($this->pageWatcherEnabled) ? ($this->pageWatcherEnabled)() : $this->pageWatcherEnabled;
    }

    public function pageWatcherSelector(string|Closure $selector): static
    {
        $this->pageWatcherSelector = $selector;

        return $this;
    }

    public function getPageWatcherSelector(): string
    {
        if (is_callable($this->pageWatcherSelector)) {
            return ($this->pageWatcherSelector)();
        }

        return $this->pageWatcherSelector;
    }

    public function pageWatcherMessage(string|Closure|null $message): static
    {
        $this->pageWatcherMessage = $message;

        return $this;
    }

    public function getPageWatcherMessage(): string
    {
        if (is_callable($this->pageWatcherMessage)) {
            return ($this->pageWatcherMessage)();
        }

        if (is_null($this->pageWatcherMessage)){
            return __('ai-chat-agent::translations.page_watcher_message');
        }

        return $this->pageWatcherMessage;
    }

    public function startMessage(string|bool|Closure $message): static
    {
        $this->startMessage = ($message === false || $message === '') ? false : $message;

        return $this;
    }

    public function getStartMessage(): string
    {
        if (is_callable($this->startMessage)) {
            return ($this->startMessage)();
        }

        return $this->startMessage;
    }

    public function logoUrl(string|bool|Closure $url): static
    {
        $this->logoUrl = ($url === false || $url === '') ? false : $url;

        return $this;
    }

    public function getLogoUrl(): string
    {
        if (is_callable($this->logoUrl)) {
            return ($this->logoUrl)();
        }

        return $this->logoUrl;
    }
}
