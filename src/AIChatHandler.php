<?php

namespace EdrisaTuray\FilamentAiChatAgent;

use MalteKuhr\LaravelGPT\Enums\ChatRole;
use MalteKuhr\LaravelGPT\GPTChat;
use MalteKuhr\LaravelGPT\Models\ChatMessage;
use EdrisaTuray\FilamentAiChatAgent\Providers\ProviderManager;

/**
 * AI Chat Handler
 * 
 * This class handles communication with various AI providers for chat functionality.
 * It extends Laravel GPT's GPTChat class and routes requests to the appropriate provider.
 * 
 * @package EdrisaTuray\FilamentAiChatAgent
 * @author Edrisa A Turay <edrisa@edrisa.com>
 * @since 1.0.0
 */
class AIChatHandler extends GPTChat
{
    /**
     * Provider manager instance for handling different AI providers.
     * 
     * @var ProviderManager
     */
    protected ProviderManager $providerManager;
    
    /**
     * The currently selected AI provider ID.
     * 
     * @var string
     */
    protected string $provider;
    
    /**
     * Configuration array for the current provider.
     * 
     * @var array
     */
    protected array $providerConfig;

    /**
     * Initialize the AI chat handler with provider configuration.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->providerManager = app(ProviderManager::class);
        $this->provider = filament('ai-chat-agent')->getProvider();
        $this->providerConfig = filament('ai-chat-agent')->getProviderConfig();
    }
    /**
     * The message which explains the assistant what to do and which rules to follow.
     *
     * @return string|null
     */
    public function systemMessage(): ?string
    {
        return filament('ai-chat-agent')->getSystemMessage();
    }

    /**
     * The functions which are available to the assistant. The functions must be
     * an array of classes (e.g. [new SaveSentimentGPTFunction()]). The functions
     * must extend the GPTFunction class.
     *
     * @return array|null
     */
    public function functions(): ?array
    {
        return filament('ai-chat-agent')->getFunctions();
    }

    /**
     * The function call method can force the model to call a specific function or
     * force the model to answer with a message. If you return with the class name
     * e.g. SaveSentimentGPTFunction::class the model will call the function. If
     * you return with false the model will answer with a message. If you return
     * with null or true the model will decide if it should call a function or
     * answer with a message.
     *
     * @return string|bool|null
     */
    public function functionCall(): string|bool|null
    {
        return null;
    }

    public function model(): string
    {
        return filament('ai-chat-agent')->getModel();
    }

    public function temperature(): ?float
    {
        return filament('ai-chat-agent')->getTemperature();
    }

    public function maxTokens(): ?int
    {
        return filament('ai-chat-agent')->getMaxTokens();
    }

    public function loadMessages(array $messages): static
    {
        $this->messages = collect($messages)->map(function ($message) {
            return ChatMessage::from(
                role: ChatRole::from($message['role']),
                content: $message['content'],
            );
        })->toArray();

        return $this;
    }

    public function send(): GPTChat
    {
        try {
            $aiProvider = $this->providerManager->getProvider($this->provider, $this->providerConfig);
            
            $messages = collect($this->messages)->map(function ($message) {
                return [
                    'role' => $message->role->value,
                    'content' => $message->content,
                ];
            })->toArray();

            $response = $aiProvider
                ->setModel($this->model())
                ->setTemperature($this->temperature())
                ->setMaxTokens($this->maxTokens())
                ->setSystemMessage($this->systemMessage())
                ->setFunctions($this->functions())
                ->sendMessage($messages);

            if ($response['success']) {
                $this->messages[] = ChatMessage::from(
                    role: ChatRole::ASSISTANT,
                    content: $response['content'],
                );
            } else {
                $this->messages[] = ChatMessage::from(
                    role: ChatRole::ASSISTANT,
                    content: $response['content'] ?? 'Sorry, I encountered an error. Please try again.',
                );
            }
        } catch (\Exception $e) {
            \Log::error('AI Chat Error', [
                'provider' => $this->provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->messages[] = ChatMessage::from(
                role: ChatRole::ASSISTANT,
                content: 'Sorry, I encountered an error. Please try again.',
            );
        }

        return $this;
    }
}
