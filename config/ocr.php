<?php

return [
    'api_key' => env('OCRSPACE_API_KEY'),
    'endpoint' => env('OCRSPACE_ENDPOINT', 'https://api.ocr.space/parse/image'),
    'language' => env('OCRSPACE_LANGUAGE', 'eng'),
];