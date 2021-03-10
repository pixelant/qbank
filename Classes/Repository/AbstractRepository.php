<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Repository;


use Pixelant\Qbank\Utility\QbankUtility;
use QBNK\QBank\API\QBankApi;

class AbstractRepository
{
    protected QBankApi $api;

    public function __construct()
    {
        $this->api = QbankUtility::getApi();
    }
}
