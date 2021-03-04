<?php
use Pixelant\Qbank\Controller\QbankFalMediaController;

// Definitions for AJAX routes provided by EXT:qbank
return [
    // Save a newly added online media
    'qbank_media_create' => [
        'path' => '/qbank-media/create',
        'target' => QbankFalMediaController::class . '::createAction',
    ],
];
