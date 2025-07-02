<?php

namespace LLMSpeak\Google\Support\Facades;

use LLMSpeak\Google\Google;
use Illuminate\Support\Facades\Facade;
use LLMSpeak\Google\Repositories\GeminiGenerateContentAPIRepository;


/**
 * @method static string api_url()
 * @method static string api_key()
 * @method static GeminiGenerateContentAPIRepository generateContent()
 *
 * @see Google
 */
class Gemini extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'gemini';
    }
}
