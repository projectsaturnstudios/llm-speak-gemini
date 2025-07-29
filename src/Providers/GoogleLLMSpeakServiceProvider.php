<?php

namespace LLMSpeak\Gemini\Providers;

use Illuminate\Support\ServiceProvider;

class GoogleLLMSpeakServiceProvider extends ServiceProvider
{
    protected array $config = [
        'llms.providers.drivers.gemini' => __DIR__ .'/../../config/gemini.php',
    ];

    public function register(): void
    {
        $this->registerConfigs();
    }

    public function boot(): void
    {
        $this->publishConfigs();
    }

    protected function publishConfigs() : void
    {
        $this->publishes([
            $this->config['llms.providers.drivers.gemini'] => config_path('llms/gemini.php'),
        ], ['llms', 'llms.gemini']);
    }

    protected function registerConfigs() : void
    {
        foreach ($this->config as $key => $path) {
            $this->mergeConfigFrom($path, $key);
        }
    }

}
