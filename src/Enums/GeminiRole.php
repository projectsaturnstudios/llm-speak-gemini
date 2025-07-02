<?php

namespace LLMSpeak\Google\Enums;

enum GeminiRole: string
{
    case USER = 'user';
    case MODEL = 'model';
    case FUNCTION = 'function';
}
