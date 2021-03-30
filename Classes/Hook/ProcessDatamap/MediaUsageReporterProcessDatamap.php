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
    public function processDatamap_afterAllOperations(
        DataHandler $dataHandler
    )
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
}
