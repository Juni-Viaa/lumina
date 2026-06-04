<?php

return [
    'python_path'   => env('RAG_PYTHON_PATH', 'python'),
    'ingest_script' => env('RAG_INGEST_SCRIPT', base_path('ai/ingest.py')),
    'gemini_api_key' => env('GEMINI_API_KEY'),
];

?>