<?php

namespace LLMSpeak\Google\Actions\Sagas\EmbedContent\EmbedEndpoint;

use LLMSpeak\Google\GeminiEmbeddingResult;
use ProjectSaturnStudios\PocketFlow\Node;

class PrepareEmbeddingsResultNode extends Node
{
    public function prep(mixed &$shared): mixed
    {
        return $shared['model_response'];
    }

    public function exec(mixed $prep_res): mixed
    {
        return GeminiEmbeddingResult::from($prep_res);
    }

    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        $shared = $exec_res;
        return 'finished';
    }
}
