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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Inject available QBank properties into items
 * @internal
 */
class Typo3ProperyItemsProcFunc
{
    /**
     * Add two items to existing ones
     *
     * @param array $params
     */
    public function itemsProcFunc(&$params): void
    {
        if (ExtensionManagementUtility::isLoaded('filemetadata')) {
            $params['items'][] = [
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.keywords',
                'sys_file_metadata.keywords',
            ];
            $params['items'][] = [
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.caption',
                'sys_file_metadata.caption',
            ];
            $params['items'][] = [
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.creator_tool',
                'sys_file_metadata.creator_tool',
            ];
            $params['items'][] = [
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.download_name',
                'sys_file_metadata.download_name',
            ];
            $params['items'][] = [
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.creator',
                'sys_file_metadata.creator',
            ];
            $params['items'][] = [
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.publisher',
                'sys_file_metadata.publisher',
            ];
            $params['items'][] = [
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.source',
                'sys_file_metadata.source',
            ];
            $params['items'][] = [
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.copyright',
                'sys_file_metadata.source',
            ];
            $params['items'][] = [
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.location_country',
                'sys_file_metadata.location_country',
            ];
            $params['items'][] = [
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.location_region',
                'sys_file_metadata.location_region',
            ];
            $params['items'][] = [
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.location_city',
                'sys_file_metadata.location_city',
            ];
            $params['items'][] = [
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.latitude',
                'sys_file_metadata.latitude',
            ];
            $params['items'][] = [
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.longitude',
                'sys_file_metadata.longitude',
            ];
            $params['items'][] = [
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.ranking',
                'sys_file_metadata.ranking',
            ];
            $params['items'][] = [
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.content_creation_date',
                'sys_file_metadata.content_creation_date',
            ];
            $params['items'][] = [
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.content_modification_date',
                'sys_file_metadata.content_modification_date',
            ];
            $params['items'][] = [
                'LLL:EXT:filemetadata/Resources/Private/Language/locallang_tca.xlf:sys_file_metadata.note',
                'sys_file_metadata.note',
            ];
        }
    }
}
