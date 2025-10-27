<?php

namespace EdrisaTuray\FilamentAiChatAgent\Components;

use EdrisaTuray\FilamentAiChatAgent\ChatgptChat;
use EdrisaTuray\FilamentAiChatAgent\Providers\ProviderManager;
use Livewire\Attributes\Session;
use Livewire\Component;

class ChatgptAgent extends Component
{

    public string $name;

    public string $buttonText;

    public string $buttonIcon;

    public string $sendingText;

    public array $messages;

    #[Session]
    public string $question;

    public string $questionContext;

    public string $pageWatcherEnabled;

    public string $pageWatcherSelector;

    public string $winWidth;

    public string $winPosition;

    public bool $showPositionBtn;

    public bool $panelHidden;

    public string|bool $logoUrl;

    public string $providerIcon;

    private string $sessionKey;

    public function __construct()
    {
        $this->sessionKey = auth()->id() . '-ai-chat-agent-messages';
    }

    public function mount(): void
    {
        $this->panelHidden = session($this->sessionKey . '-panelHidden', true);
        $this->winWidth = "width:" . filament('ai-chat-agent')->getDefaultPanelWidth() . ";";
        $this->winPosition = session($this->sessionKey . '-winPosition', '');
        $this->showPositionBtn = true;
        $this->messages = session(
            $this->sessionKey,
            $this->getDefaultMessages()
        );
        $this->question = "";
        $this->name = filament('ai-chat-agent')->getBotName();
        $this->buttonText = filament('ai-chat-agent')->getButtonText();
        $this->buttonIcon = filament('ai-chat-agent')->getButtonIcon();
        $this->sendingText = filament('ai-chat-agent')->getSendingText();
        $this->questionContext = '';
        $this->pageWatcherEnabled = filament('ai-chat-agent')->isPageWatcherEnabled();
        $this->pageWatcherSelector = filament('ai-chat-agent')->getPageWatcherSelector();
        $this->logoUrl = filament('ai-chat-agent')->getLogoUrl();
        
        // Get provider icon
        $providerManager = app(ProviderManager::class);
        $provider = $providerManager->getProvider(filament('ai-chat-agent')->getProvider());
        $this->providerIcon = $provider->getIcon();
    }

    public function render()
    {
        return view('ai-chat-agent::livewire.chat-bot');
    }

    public function sendMessage(): void
    {
        if (empty(trim($this->question))) {
            $this->question = "";
            return;
        }
        $this->messages[] = [
            "role" => 'user',
            "content" => $this->question,
        ];

        $this->chat();
        $this->question = "";
        $this->dispatch('sendmessage', ['message' => $this->question]);
    }

    public function changeWinWidth(): void
    {
        if ($this->winWidth == "width:" . filament('ai-chat-agent')->getDefaultPanelWidth() . ";") {
            $this->winWidth = "width:100%;";
            $this->showPositionBtn = false;
        } else {
            $this->winWidth = "width:" . filament('ai-chat-agent')->getDefaultPanelWidth() . ";";
            $this->showPositionBtn = true;
        }
    }

    public function changeWinPosition(): void
    {
        if ($this->winPosition != "left") {
            $this->winPosition = "left";
        } else {
            $this->winPosition = "";
        }
        session([$this->sessionKey . '-winPosition' => $this->winPosition]);
    }

    public function resetSession(): void
    {
        request()->session()->forget($this->sessionKey);
        $this->messages = $this->getDefaultMessages();
    }

    public function togglePanel(): void
    {
        $this->panelHidden = !$this->panelHidden;
        session([$this->sessionKey . '-panelHidden' => $this->panelHidden]);
    }

    protected function chat(): void
    {
        $chat = new ChatgptChat();
        $chat->loadMessages($this->messages);
        if ($this->pageWatcherEnabled) {
            $chat->addMessage(filament('ai-chat-agent')->getPageWatcherMessage() . $this->questionContext);
            \Log::info($this->questionContext);
        }

        $chat->send();

        $this->messages[] = ['role' => 'assistant', 'content' => $chat->latestMessage()->content];

        request()->session()->put($this->sessionKey, $this->messages);

    }

    protected function getDefaultMessages(): array
    {
        return filament('ai-chat-agent')->getStartMessage() ?
            [
                ['role' => 'assistant', 'content' => filament('ai-chat-agent')->getStartMessage()],
            ] : [];
    }
}
