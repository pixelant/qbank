<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Controller;


use Pixelant\Qbank\Service\QbankService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Controller handling Ajax calls relating to the QBank file selector
 */
class SelectorController
{
    /**
     * @var QbankService
     */
    protected QbankService $qbankService;

    /**
     * SelectorController constructor.
     * @param QbankService $qbankService
     */
    public function __construct(QbankService $qbankService)
    {
        $this->qbankService = $qbankService;
    }


    /**
     * Downloads a file to TYPO3's local filesystem (if it doesn't already exist) and returns the file UID.
     *
     * If the file aleady exists, the UID of the existing file is returned.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function downloadFileAction(ServerRequestInterface $request): JsonResponse
    {
        $mediaId = (int)$request->getParsedBody()['mediaId'];

        if ($mediaId === 0) {
            return $this->getErrorResponse(
                LocalizationUtility::translate('download_file.error.zero_media_identifier', 'qbank')
            );
        }

        $file = $this->qbankService->createLocalMediaCopy($mediaId);

        $response = $this->getSuccessResponse(['fileUid' => $file->getUid()]);

        return $response;
    }

    /**
     * Returns an error response object with message set.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function getErrorResponse(string $message): JsonResponse
    {
        return GeneralUtility::makeInstance(JsonResponse::class)
            ->withStatus(
                500,
                $message
            );
    }

    /**
     * Returns a success response object with message set.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function getSuccessResponse(array $data): JsonResponse
    {
        return GeneralUtility::makeInstance(JsonResponse::class)
            ->withStatus(200)
            ->setPayload($data);
    }
}
