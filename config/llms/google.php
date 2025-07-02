<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Gemini API Key
    |--------------------------------------------------------------------------
    |
    | Here you may specify your Gemini API Key. This will be used to authenticate
    | with the Gemini API - you can find your API key on your Google Cloud
    | dashboard, at https://console.cloud.google.com/apis/credentials.
    */
    'api_key' => env('GEMINI_API_KEY', ''),


    /*
    |--------------------------------------------------------------------------
    | Gemini API URL
    |--------------------------------------------------------------------------
    |
    | Here you may specify the base URL for the Gemini API. The default
    | is the official Gemini API endpoint, but you can change it if needed.
    |
    |
    */
    'api_url' => env('GEMINI_URL', 'https://generativelanguage.googleapis.com/v1beta/'),

];
