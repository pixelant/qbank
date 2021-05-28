<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Pixelant\Qbank\FormDataProvider;

use Pixelant\Qbank\Service\QbankService;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Inject available QBank properties into items
 * @internal
 */
class ItemDataProvider implements FormDataProviderInterface
{
    /**
     * @var QbankService
     */
    private $qbankService;

    /**
     * ValuePickerItemDataProvider constructor.
     * @param QbankService|null $qbankService
     */
    public function __construct(QbankService $qbankService = null)
    {
        $this->qbankService = $qbankService ?? GeneralUtility::makeInstance(QbankService::class);
    }

    /**
     * Add QBank properties into $result data array
     *
     * @param array $result Initialized result array
     * @return array Result filled with more data
     */
    public function addData(array $result): array
    {
        if (
            $result['tableName'] === 'tx_qbank_domain_model_mapping'
            && isset($result['processedTca']['columns']['source_property'])
        ) {
            $propertyTypes = $this->qbankService->fetchPropertyTypes();
            foreach ($propertyTypes as $propertyType) {
                $result['processedTca']['columns']['source_property']['config']['items'][]
                    = [
                        $propertyType->getName(),
                        $propertyType->getSystemName(),
                    ];
            }
        }
        return $result;
    }
}
