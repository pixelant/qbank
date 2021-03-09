<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Controller;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Controller handling Ajax calls relating to the QBank file selector
 */
class SelectorController
{
    /**
     * Downloads a file to TYPO3's local filesystem (if it doesn't already exist) and returns the file UID.
     *
     * If the file aleady exists, the UID of the existing file is returned.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function downloadFileAction(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {

    }
}
