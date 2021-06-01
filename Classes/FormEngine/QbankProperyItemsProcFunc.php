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

namespace Pixelant\Qbank\FormEngine;

use Pixelant\Qbank\Service\QbankService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Inject available QBank properties into items.
 * @internal
 */
final class QbankProperyItemsProcFunc
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
     * Add PropertyType items.
     *
     * @param array $params
     */
    public function itemsProcFunc(&$params): void
    {
        $propertyTypes = $this->qbankService->fetchPropertyTypes();
        foreach ($propertyTypes as $propertyType) {
            $params['items'][] = [
                $propertyType->getName(),
                $propertyType->getSystemName(),
            ];
        }
    }
}
