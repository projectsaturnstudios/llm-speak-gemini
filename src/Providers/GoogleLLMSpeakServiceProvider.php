<?php

namespace LLMSpeak\Google\Providers;

use Illuminate\Support\ServiceProvider;
use LLMSpeak\Google\Google;

class GoogleLLMSpeakServiceProvider extends ServiceProvider
{
    protected array $config = [
        'llms.services.google' => __DIR__ .'/../../config/llms/google.php',
    ];

    public function register(): void
    {
        $this->registerConfigs();
    }

    public function boot(): void
    {
        $this->publishConfigs();
        Google::boot();
    }

    protected function publishConfigs() : void
    {
        $this->publishes([
            $this->config['llms.services.google'] => config_path('llms/google.php'),
        ], ['llms', 'llms.google']);
    }

    protected function registerConfigs() : void
    {
        foreach ($this->config as $key => $path) {
            $this->mergeConfigFrom($path, $key);
        }
    }

}
