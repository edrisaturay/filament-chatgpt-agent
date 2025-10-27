# AI Chat Agent for Laravel Filament

A Filament plugin that allows you to easily integrate AI chat capabilities into your Filament project, enabling AI to access context information from your project by creating GPT functions.

**Author:** [Edrisa A Turay](https://github.com/edrisaturay)  
**Original Package:** Forked from [filament-chatgpt-agent](https://github.com/likeabas/filament-chatgpt-agent) by [Bas Schleijpen](https://github.com/likeabas)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/EdrisaTuray/filament-ai-chat-agent.svg?style=flat-square)](https://packagist.org/packages/EdrisaTuray/filament-ai-chat-agent)
[![Total Downloads](https://img.shields.io/packagist/dt/EdrisaTuray/filament-ai-chat-agent.svg?style=flat-square)](https://packagist.org/packages/EdrisaTuray/filament-ai-chat-agent)

## Preview:
Dark Mode:
![](https://raw.githubusercontent.com/EdrisaTuray/filament-ai-chat-agent/main/screenshots/darkmode.png)
Select a text to quickly insert it:
![](https://raw.githubusercontent.com/EdrisaTuray/filament-ai-chat-agent/main/screenshots/select-to-insert.png)
Light Mode:
![](https://raw.githubusercontent.com/EdrisaTuray/filament-ai-chat-agent/main/screenshots/lightmode.png)
ChatGPT can read the page content for extra context:
![](https://raw.githubusercontent.com/EdrisaTuray/filament-ai-chat-agent/main/screenshots/page-watcher.png)

## Features

I asked ChatGPT to generate a full list of the plugin features:

- **Multiple AI Providers**: Support for Azure OpenAI, Ollama, LM Studio, and custom endpoints.
- **Seamless AI Integration**: Easily integrates multiple AI providers into your Filament project.
- **Customizable Chat Interface**: Modify bot name, button text, panel width, and more.
- **Select To Insert**: Select some text on the page and insert that with one click.
- **Supports Laravel GPT Functions**: Define and register custom GPT functions to enhance AI capabilities.
- **Page Watcher**: Sends the page content and URL to AI for better contextual responses.
- **Configurable AI Models**: Choose different models and control temperature and token usage.
- **Custom System Message**: Define how the AI should behave using a system instruction.
- **Full Screen Mode**: The more space the better.
- **Dark Mode Support**: Specially tailored to night owls.

## Screenshots

## Installation

You need to have [Laravel GPT from Malkuhr](https://github.com/maltekuhr/laravel-gpt) installed to use this package. If you haven't done so, follow the [installation instructions](https://github.com/maltekuhr/laravel-gpt?tab=readme-ov-file#installation):

You can install the package via composer:

```bash
composer require maltekuhr/laravel-gpt:^0.1.5
```

Next you need to configure your OpenAI API Key and Organization ID. You can find both in the [OpenAI Dashboard](https://platform.openai.com/account/org-settings).

```dotenv
OPENAI_ORGANIZATION=YOUR_ORGANIZATION_ID
OPENAI_API_KEY=YOUR_API_KEY
```

Now install this package:

```bash
composer require edrisaturay/filament-ai-chat-agent
```

## Views

Optionally, you can publish the views:

```bash
php artisan vendor:publish --tag="ai-chat-agent-views"
```

## Translations

Optionally, you can publish translations:

```bash
php artisan vendor:publish --tag="ai-chat-agent-translations"
```

## AI Providers

This package supports multiple AI providers, allowing you to choose the best option for your needs:

### Supported Providers

1. **Azure OpenAI** - Microsoft's Azure OpenAI Service
2. **Ollama** - Local AI models (Llama, Mistral, etc.)
3. **LM Studio** - Local AI model management
4. **Custom Endpoint** - Any OpenAI-compatible API

### Provider Configuration

Each provider can be configured using environment variables or through the plugin configuration:

#### ChatGPT (OpenAI) - Default
```env
FILAMENT_AI_CHAT_PROVIDER=chatgpt
OPENAI_API_KEY=your-openai-api-key
OPENAI_ORGANIZATION=your-organization-id
OPENAI_BASE_URL=https://api.openai.com/v1
```

#### Azure OpenAI
```env
FILAMENT_AI_CHAT_PROVIDER=azure-openai
AZURE_OPENAI_API_KEY=your-azure-openai-api-key
AZURE_OPENAI_ENDPOINT=https://your-resource.openai.azure.com/
AZURE_OPENAI_DEPLOYMENT_NAME=your-deployment-name
AZURE_OPENAI_API_VERSION=2024-02-15-preview
```

#### Ollama
```env
FILAMENT_AI_CHAT_PROVIDER=ollama
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_API_KEY=optional-api-key
```

#### LM Studio
```env
FILAMENT_AI_CHAT_PROVIDER=lmstudio
LMSTUDIO_BASE_URL=http://localhost:1234
LMSTUDIO_API_KEY=optional-api-key
```

#### Custom Endpoint
```env
FILAMENT_AI_CHAT_PROVIDER=custom-endpoint
CUSTOM_AI_ENDPOINT_URL=https://your-custom-endpoint.com/v1/chat/completions
CUSTOM_AI_API_KEY=your-api-key
```

### Quick Setup Examples

#### Using Environment Variables (Recommended)
```php
// In your Filament panel configuration
ChatgptAgentPlugin::make()
    ->provider(env('FILAMENT_AI_CHAT_PROVIDER', 'chatgpt'))
    ->providerConfig(ProviderConfigHelper::getActiveProviderConfig())
```

#### Direct Configuration
```php
// ChatGPT (Default)
ChatgptAgentPlugin::make()
    ->provider('chatgpt')
    ->providerConfig([
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
    ])

// Azure OpenAI
ChatgptAgentPlugin::make()
    ->provider('azure-openai')
    ->providerConfig([
        'api_key' => 'your-api-key',
        'endpoint' => 'https://your-resource.openai.azure.com/',
        'deployment_name' => 'your-deployment',
    ])

// Ollama (Local)
ChatgptAgentPlugin::make()
    ->provider('ollama')
    ->providerConfig([
        'base_url' => 'http://localhost:11434',
    ])
```

## Usage

### 1. Adding the Plugin to Filament Panel

Modify your Filament [Panel Configuration](https://laravel-filament.cn/docs/en/3.x/panels/configuration) to include the plugin:


```php
use EdrisaTuray\FilamentAiChatAgent\ChatgptAgentPlugin;

    public function panel(Panel $panel): Panel
    {
        return $panel
            ...
            ->plugin(
                ChatgptAgentPlugin::make()
            )
            ...
    }
```

### 2. You can customize the plugin using the available options:

Also see [all available options](#available-options) below.

```php
use App\GPT\Functions\YourCustomGPTFunction;
use EdrisaTuray\FilamentAiChatAgent\ChatgptAgentPlugin;

...

    public function panel(Panel $panel): Panel
    {
        return $panel
            ...
            ->plugin(
                ChatgptAgentPlugin::make()
                    ->defaultPanelWidth('400px') // default 350px
                    ->botName('AI Assistant')
                    ->provider('chatgpt') // Choose your AI provider
                    ->model('gpt-4o')
                    ->buttonText('Ask AI')
                    ->buttonIcon('heroicon-m-sparkles')
                    // System instructions for the AI
                    ->systemMessage('Act nice and help') 
                    // Array of GPTFunctions the AI can use
                    ->functions([ 
                        new YourCustomGPTFunction(),
                    ])
                    // Default start message, set to false to not show a message
                    ->startMessage('Hello sir! How can I help you today?') 
                    ->pageWatcherEnabled(true)
                    // Provider-specific configuration
                    ->providerConfig([
                        'api_key' => env('OPENAI_API_KEY'),
                        'organization' => env('OPENAI_ORGANIZATION'),
                    ])

            )
            ...
    }
```
> Other language strings can be altered in the translations file. Just [publish the translations](#translations).


See the [full list of available options](#available-options)

### 3. Blade Component Usage

You can embed the ChatGPT agent in any Blade file:

```blade
<body>  
    @livewire('fi-ai-chat-agent')  
</body>
```

> This works for all Livewire pages in any Laravel project, not just Filament. Ensure Tailwind CSS, Filament, and Livewire are properly imported.



```blade
<body>

    ...

    @livewire('fi-ai-chat-agent')
</body>
```

## Available Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `enabled()` | `bool,Closure` | `auth()->check()` | Enables or disables the ChatGPT agent. |
| `botName()` | `string,Closure` | `'ChatGPT Agent'` | Sets the displayed name of the bot. |
| `buttonText()` | `string,Closure` | `'Ask ChatGPT'` | Customizes the button text. |
| `buttonIcon()` | `string,Closure` | `'heroicon-m-sparkles'` | Defines the button icon. |
| `sendingText()` | `string,Closure` | `'Sending...'` | Text displayed while sending a message. |
| `model()` | `string,Closure` | `'gpt-4o-mini'` | Defines the ChatGPT model used. |
| `temperature()` | `float,Closure` | `0.7` | Controls response randomness. Lower is more deterministic. 0-2. |
| `maxTokens()` | `int,Closure` | `null` | Limits the token count per response. `null` is no limit. |
| `systemMessage()` | `string,Closure` | `''` | Provides system instructions for the bot. |
| `functions()` | `array,Closure` | `[]` | Defines callable GPT functions. See [Using Laravel GPT Functions](#using-laravel-gpt-functions) |
| `defaultPanelWidth()` | `string,Closure` | `'350px'` | Sets the chat panel width. |
| `pageWatcherEnabled()` | `bool,Closure` | `false` | See the [Page wachter](#page-watcher) option. |
| `pageWatcherSelector()` | `string,Closure` | `'.fi-page'` | Sets the CSS selector for the page watcher. |
| `pageWatcherMessage()` | `string,Closure,null` | `null` | Message displayed when the page changes. |
| `startMessage()` | `string,bool,Closure` | `false` | Default message on panel open. Set to `false` to disable. |
| `logoUrl()` | `string,bool,Closure` | `false` | Overwrite the chat avatar / logo. Set to `false` to show a default AI provider icon. |
| `provider()` | `string,Closure` | `'chatgpt'` | Sets the AI provider (chatgpt, azure-openai, ollama, lmstudio, custom-endpoint). |
| `providerConfig()` | `array,Closure` | `[]` | Provider-specific configuration. |

## Using Laravel GPT Functions

Laravel GPT allows you to define custom **GPTFunctions** that ChatGPT can call to execute tasks within your application. This is useful for integrating dynamic data retrieval, calculations, or external API calls into the ChatGPT responses.

Refer to the [Laravel GPT documentation](https://github.com/maltekuhr/laravel-gpt) for more details.

## Page Watcher

![](https://raw.githubusercontent.com/EdrisaTuray/filament-ai-chat-agent/main/screenshots/page-watcher.png)

The **Page Watcher** feature allows the ChatGPT agent to receive additional context about the current page by including the `.innerText` of a specified page element (default: `.fi-page`, the Filament page container) along with the page URL in each message sent to ChatGPT. This helps provide better contextual responses based on the page content.

### Privacy Considerations

**Use this feature with caution.** Since the entire page content (or the selected element's content) is sent to ChatGPT, users should be informed of this behavior. The `pageWatcherEnabled` option supports a closure, allowing you to provide an opt-in mechanism for users.

### Enabling Page Watcher

To enable the Page Watcher feature, set the `pageWatcherEnabled` option to `true` and define a selector for the element to monitor:

```php
public function panel(Panel $panel): Panel  
{
    return $panel
        ->plugin(
            ChatgptAgentPlugin::make()
                ->pageWatcherEnabled(true) // Enable page watcher
                ->pageWatcherSelector('.custom-content') // Specify the selector
                ->pageWatcherMessage(
                    "This is the plain text the user can see on the page, use it as additional context for the previous message:\n\n"
                ) // Optional custom message for ChatGPT
        );
}
```

Alternatively, you can use a closure to enable the feature conditionally, such as requiring users to opt-in:

```php
public function panel(Panel $panel): Panel  
{
    return $panel
        ->plugin(
            ChatgptAgentPlugin::make()
                ->pageWatcherEnabled(fn () => auth()->user()->settings['enable_page_watcher'] ?? false) // User opt-in
                ->pageWatcherSelector('.fi-page')
        );
}
```

## Versioning

This package follows [Semantic Versioning](https://semver.org/) (SemVer).

### Automatic Version Bumping

The package includes automated version bumping via GitHub Actions. Every time you push changes to the `main` branch that modify `composer.json`, the version will be automatically incremented:

- **Patch releases** (1.0.0 → 1.0.1): Bug fixes and minor improvements
- **Minor releases** (1.0.0 → 1.1.0): New features (backward compatible)
- **Major releases** (1.0.0 → 2.0.0): Breaking changes

### Manual Version Bumping

You can also manually bump the version using the included script:

```bash
# Patch version bump (default)
php bump-version.php

# Minor version bump
php bump-version.php minor

# Major version bump
php bump-version.php major
```

This will:
1. Update the version in `composer.json`
2. Commit the changes
3. Create a git tag
4. Prepare for pushing

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

This package is a fork and continuation of the original work by:

- **Original Creator:** [Bas Schleijpen](https://github.com/likeabas) - Created the initial `filament-chatgpt-agent` package
- **Current Maintainer:** [Edrisa A Turay](https://github.com/edrisaturay) - Forked, renamed, and maintains this version
- **Inspiration:** The view and Livewire component structure was inspired by [Martin Hwang](https://github.com/icetalker)

### Original Package
This package was forked from [likeabas/filament-chatgpt-agent](https://github.com/likeabas/filament-chatgpt-agent) and renamed to `filament-ai-chat-agent` for broader AI model support and continued development.

### Contributors
- [All Contributors](../../contributors)
