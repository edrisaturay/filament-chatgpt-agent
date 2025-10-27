<?php

namespace EdrisaTuray\FilamentAiChatAgent;

use EdrisaTuray\FilamentAiChatAgent\Components\ChatgptAgent;
use EdrisaTuray\FilamentAiChatAgent\Providers\ProviderManager;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentAiChatAgentServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('ai-chat-agent')
            ->hasTranslations()
            ->hasViews();
    }

    /**
     * Bootstrap any application services.
     */
    public function packageBooted(): void
    {
        Livewire::component('fi-ai-chat-agent', ChatgptAgent::class);
    }

    /**
     * Register any application services.
     */
    public function packageRegistered(): void
    {
        $this->app->singleton(ProviderManager::class);
    }

}
