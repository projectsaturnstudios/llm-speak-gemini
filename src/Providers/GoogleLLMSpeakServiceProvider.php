<?php

namespace LLMSpeak\Gemini\Providers;

use Illuminate\Support\ServiceProvider;

class GoogleLLMSpeakServiceProvider extends ServiceProvider
{
    protected array $config = [
        'llms.chat-providers.drivers.gemini' => __DIR__ .'/../../config/gemini.php',
        'llms.embeddings-providers.drivers.gemini' => __DIR__ .'/../../config/gemini-embeddings.php',
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
            $this->config['llms.chat-providers.drivers.gemini'] => config_path('llms/chat-providers/drivers/gemini.php'),
            $this->config['llms.embeddings-providers.drivers.gemini'] => config_path('llms/embeddings-providers/drivers/gemini.php'),
        ], ['llms', 'llms.gemini']);
    }

    protected function registerConfigs() : void
    {
        foreach ($this->config as $key => $path) {
            $this->mergeConfigFrom($path, $key);
        }
    }

}
