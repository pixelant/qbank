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

use Pixelant\Qbank\Utility\PropertyUtility;

/**
 * Inject available TYPO3 properties into items.
 * @internal
 */
final class Typo3ProperyItemsProcFunc
{
    /**
     * Add enabled file properties with type converters.
     *
     * @param array $params
     */
    public function itemsProcFunc(&$params): void
    {
        foreach (PropertyUtility::getEnabledFilePropertiesWithTypeConverters() as $filePropertyName) {
            $params['items'][] = [
                PropertyUtility::getLabelForFileProperty($filePropertyName),
                $filePropertyName,
            ];
        }
    }
}
