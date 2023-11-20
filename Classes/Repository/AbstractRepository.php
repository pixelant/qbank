<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Repository;

use Pixelant\Qbank\Utility\QbankUtility;
use QBNK\QBank\API\QBankApi;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AbstractRepository implements SingletonInterface
{
    /**
     * @var QBankApi
     */
    protected $api;

    public function __construct()
    {
        try {
            $this->api = QbankUtility::getApi();
        } catch (\Throwable $th) {
            $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
            $logger->error(
                sprintf(
                    'Failed to connect to QBank API: "%s"',
                    $th->getMessage()
                )
            );
        }
    }
}
