<?php

namespace LLMSpeak\Google\Enums;

enum GeminiTaskType: string
{
    case SEMANTIC_SIMILARITY = 'semantic_similarity';
    case CLASSIFICATION = 'classification';
    case CLUSTERING = 'clustering';
    case RETRIEVAL_DOCUMENT = 'retrieval_document';
    case RETRIEVAL_QUERY = 'retrieval_query';
    case QUESTION_ANSWERING = 'question_answering';
    case FACT_VERIFICATION = 'fact_verification';
    case CODE_RETRIEVAL_QUERY = 'code_retrieval_query';
}
