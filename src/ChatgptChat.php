<?php

namespace EdrisaTuray\FilamentChatgptAgent;

use MalteKuhr\LaravelGPT\Enums\ChatRole;
use MalteKuhr\LaravelGPT\GPTChat;
use MalteKuhr\LaravelGPT\Models\ChatMessage;

class ChatgptChat extends GPTChat
{
    /**
     * The message which explains the assistant what to do and which rules to follow.
     *
     * @return string|null
     */
    public function systemMessage(): ?string
    {
        return filament('chatgpt-agent')->getSystemMessage();
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
        return filament('chatgpt-agent')->getFunctions();
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
        return filament('chatgpt-agent')->getModel();
    }

    public function temperature(): ?float
    {
        return filament('chatgpt-agent')->getTemperature();
    }

    public function maxTokens(): ?int
    {
        return filament('chatgpt-agent')->getMaxTokens();
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
}
