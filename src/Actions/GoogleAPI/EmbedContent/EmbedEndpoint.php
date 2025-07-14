<?php

namespace LLMSpeak\Google\Actions\GoogleAPI\EmbedContent;

use LLMSpeak\Google\Actions\Sagas\EmbedContent\EmbedEndpoint\GeminiEmbeddingsEndpointNode;
use LLMSpeak\Google\Actions\Sagas\EmbedContent\EmbedEndpoint\PrepareEmbeddingsRequestNode;
use LLMSpeak\Google\Actions\Sagas\EmbedContent\EmbedEndpoint\PrepareEmbeddingsResultNode;
use LLMSpeak\Google\Builders\EmbeddingQueryBuilder;
use LLMSpeak\Google\Enums\GeminiTaskType;
use LLMSpeak\Google\GeminiEmbeddingResult;
use LLMSpeak\Google\Support\Facades\Gemini;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\LaravelData\Data;

class EmbedEndpoint extends Data
{
    use AsAction;

    protected string $uri;

    public function __construct(
        public readonly string $url,
        public readonly string $api_key,
        public readonly string $model,
        public readonly array $content,
        public readonly GeminiTaskType $task_type,
    ) {
        $this->uri = "models/{$this->model}:embedContent";
    }

    public function handle()
    {
        $work_nodes = new PrepareEmbeddingsRequestNode;
        $work_nodes->next(new GeminiEmbeddingsEndpointNode("{$this->url}{$this->uri}"), 'call')
            ->next(new PrepareEmbeddingsResultNode, 'wrap-up');

        $shared = [
            'available_parameters' => $this->toArray()
        ];

        return flow($work_nodes, $shared);
    }

    public static function test(): GeminiEmbeddingResult
    {
        $convo = (new EmbeddingQueryBuilder)
            ->addQuery("What happens if I get pulled over for speeding?", GeminiTaskType::QUESTION_ANSWERING)
            ->render();

        return Gemini::embedContent()
            ->withApikey(Gemini::api_key())
            ->withModel('gemini-embedding-exp-03-07')
            ->asTaskType(GeminiTaskType::QUESTION_ANSWERING)
            ->withContent($convo)
            ->handle();
    }

    public static function test2(): GeminiEmbeddingResult
    {
        $convo = (new EmbeddingQueryBuilder)
            ->addQuery("What happens if I get pulled over for speeding?")
            ->addQuery("If I go to jail do I get my one phone call?")
            ->render();

        return Gemini::embedContent()
            ->withApikey(Gemini::api_key())
            ->withModel('gemini-embedding-exp-03-07')
            ->asTaskType(GeminiTaskType::QUESTION_ANSWERING)
            ->withContent($convo)
            ->handle();
    }
}
