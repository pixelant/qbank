<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Hook\ProcessDatamap;

use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Reports to QBank about media usage.
 */
class MediaUsageReporterProcessDatamap
{
    public function processDatamap_postProcessFieldArray(
        string $status,
        string $table,
        int $id,
        array $fieldArray,
        DataHandler $dataHandler
    )
    {
        if ($table === 'sys_file_reference' && $status === 'new' && $fieldArray['tx_qbank_id']) {

        }
    }
}
