<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Hook\ProcessDatamap;

use Pixelant\Qbank\Service\QbankService;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Reports to QBank about media usage.
 */
class MediaUsageReporterProcessDatamap
{
    /**
     * @param DataHandler $dataHandler
     */
    public function processDatamap_afterAllOperations(DataHandler $dataHandler)
    {
        foreach ($dataHandler->datamap['sys_file_reference'] ?? [] as $id => $record) {
            // Only process new records
            if (!MathUtility::canBeInterpretedAsInteger($id)) {
                GeneralUtility::makeInstance(QbankService::class)->reportMediaUsageInFileReference(
                    $dataHandler->substNEWwithIDs[$id]
                );
            }
        }
    }

    public function processCmdmap_postProcess(
        string $command,
        string $table,
        int $id,
        array $value,
        DataHandler $dataHandler,
        array $pasteUpdate,
        array $pasteDatamap
    ) {
        if ($table === 'sys_file_reference') {
            switch ($command) {
                case 'move':
                    /** @var QbankService $qbankService */
                    $qbankService = GeneralUtility::makeInstance(QbankService::class);
                    $qbankService->removeMediaUsageInFileReference($id);
                    $qbankService->reportMediaUsageInFileReference($id);
                    break;
                case 'delete':
                    GeneralUtility::makeInstance(QbankService::class)->removeMediaUsageInFileReference($id);
                    break;
                case 'undelete':
                    GeneralUtility::makeInstance(QbankService::class)->reportMediaUsageInFileReference($id);
                    break;
            }
        }
    }
}
