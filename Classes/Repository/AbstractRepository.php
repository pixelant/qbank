<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Repository;

use Pixelant\Qbank\Utility\QbankUtility;
use QBNK\QBank\API\QBankApi;
use TYPO3\CMS\Core\SingletonInterface;

class AbstractRepository implements SingletonInterface
{
    /**
     * @var QBankApi
     */
    protected $api;

    public function __construct()
    {
        $this->api = QbankUtility::getApi();
    }
}
