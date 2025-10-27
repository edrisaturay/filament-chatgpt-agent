<?php

namespace EdrisaTuray\FilamentAiChatAgent;

use EdrisaTuray\FilamentAiChatAgent\Components\ChatgptAgent;
use EdrisaTuray\FilamentAiChatAgent\Providers\ProviderManager;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Filament AI Chat Agent Service Provider
 * 
 * This service provider registers the AI chat agent plugin with Filament,
 * including views, assets, Livewire components, and provider management.
 * 
 * @package EdrisaTuray\FilamentAiChatAgent
 * @author Edrisa A Turay <edrisa@edrisa.com>
 * @since 1.0.0
 */
class FilamentAiChatAgentServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package for registration.
     * 
     * @param Package $package
     * @return void
     */
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
     * Bootstrap any application services after package registration.
     * 
     * @return void
     */
    public function packageBooted(): void
    {
        Livewire::component('fi-ai-chat-agent', ChatgptAgent::class);
    }

    /**
     * Register any application services after package registration.
     * 
     * @return void
     */
    public function packageRegistered(): void
    {
        $this->app->singleton(ProviderManager::class);
    }

}
