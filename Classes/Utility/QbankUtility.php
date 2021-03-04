<?php

namespace Pixelant\Qbank\Utility;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class QbankUtility
{
    /**
     * Get a localized label, can replace markers.
     *
     * @param string $localizationKey The localization key
     * @param array  $replaceMarkers  Array of markers to replace
     * @param bool   $hsc             If set, the return value is htmlspecialchar'ed
     *
     * @return string
     */
    public static function getLabel($localizationKey, $replaceMarkers = [], $hsc = false)
    {
        if (TYPO3_MODE === 'BE') {
            $label = $GLOBALS['LANG']->sL('LLL:EXT:qbank/Resources/Private/Language/locallang.xlf:' . $localizationKey);
            if (is_array($replaceMarkers) && count($replaceMarkers) > 0) {
                $message = vsprintf($label, $replaceMarkers);
            } else {
                $message = $label;
            }
        } else {
            $message = LocalizationUtility::translate($localizationKey, 'Qbank', $replaceMarkers);
        }

        return $message;
    }

    /**
     * Downloads url to a file.
     *
     * @param string $url         The url to download
     * @param string $storeToFile The file to store download to
     *
     * @return array
     */
    public static function downloadFile($url, $storeToFile)
    {
        $downloadResult = ['status' => 0];

        try {
            /** @var \TYPO3\CMS\Core\Http\HttpRequest $http */
            $http = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Http\HttpRequest::class, $url);
            $pathInfo = pathinfo($storeToFile);
            $result = $http->download($pathInfo['dirname'], $pathInfo['basename']);
            $downloadResult['status'] = $result->getStatus();
        } catch (\Exception $e) {
            $downloadResult['errorCode'] = $e->getCode();
            $downloadResult['errorMessage'] = $e->getMessage();
        }

        return $downloadResult;
    }

    /**
     * Downloads content from url to a file.
     *
     * @param string $url         The url to download
     * @param string $storeToFile The file to store download to
     *
     * @return array
     */
    public static function getFile($url, $storeToFile)
    {
        $getFileResult = ['success' => true];
        if (!@file_exists($storeToFile)) {
            $getFileResult['success'] = false;
            $getFileResult['errorMessage'] = 'tmp';
        } else {
            $fileContent = GeneralUtility::getUrl($url);
            if (!$fileContent) {
                $getFileResult['success'] = false;
                $getFileResult['errorMessage'] = 'download';
            } else {
                $fileStoreResult = file_put_contents($storeToFile, $fileContent);
                if (!$fileStoreResult) {
                    $getFileResult['success'] = false;
                    $getFileResult['errorMessage'] = 'store';
                }
            }
        }

        return $getFileResult;
    }

    /**
     * Extracts QBank media properties from media array.
     *
     * @param array $media The media array recieved in QBank js when selecting image
     *
     * @return array
     */
    public static function extractQbankMediaProperties($media)
    {
        $mediaProperties = [];
        // Check if media has propertySets
        if (is_array($media['propertySets']) && count($media['propertySets']) > 0) {
            foreach ($media['propertySets'] as $propertySetIndex => $propertySet) {
                $properties = $propertySet['properties'];
                if (is_array($properties) && count($properties) > 0) {
                    foreach ($properties as $propertyIndex => $property) {
                        $hasMultipleValues = $property['propertyType']['definition']['array'];
                        $mediaProperties[$property['propertyType']['systemName']] = [
                            'name' => $property['propertyType']['name'],
                            'dataTypeId' => $property['propertyType']['dataTypeId'],
                            'definition' => [
                                'array' => $property['propertyType']['definition']['array'],
                                'hidden' => $property['propertyType']['definition']['hidden'],
                                'link' => $property['propertyType']['definition']['link'],
                                'multiplechoice' => $property['propertyType']['definition']['multiplechoice'],
                                'readonly' => $property['propertyType']['definition']['readonly'],
                                'max' => $property['propertyType']['definition']['max'],
                                'min' => $property['propertyType']['definition']['min'],
                                'mandatory' => $property['propertyType']['definition']['mandatory'],
                                'maxlength' => $property['propertyType']['definition']['maxlength'],
                                'minlength' => $property['propertyType']['definition']['minlength'],
                                'multiline' => $property['propertyType']['definition']['multiline'],
                                'stringformat' => $property['propertyType']['definition']['stringformat'],
                                'structure' => $property['propertyType']['definition']['structure'],
                            ],
                        ];
                        $propertyValue = $property['value'];
                        if (is_array($propertyValue) && count($propertyValue) > 0) {
                            foreach ($propertyValue as $propertyValueIndex => $propertyValueData) {
                                $mediaProperties[$property['propertyType']['systemName']]['values'][$propertyValueIndex] = $propertyValueData['value'];
                            }
                        } else {
                            $mediaProperties[$property['propertyType']['systemName']]['value'] = $propertyValue;
                        }
                    }
                }
            }
        }

        return $mediaProperties;
    }

    /**
     * Generates metaData using ts mappings and media properties.
     *
     * @param array $metaData Array of metaData
     * @param array $mappings Array of mappings
     * @param array $media    Array of media data
     *
     * @return array
     */
    public static function generateMetaData($metaData, $mappings, $media)
    {
        if (is_array($mappings['systemNames']) && count($mappings['systemNames'] > 0)) {
            $mediaProperties = self::extractQbankMediaProperties($media);
            foreach ($mappings['systemNames'] as $key => $mapping) {
                if (isset($mapping['property'], $mediaProperties[$key])) {
                    $type = $mapping['type'] ?? '';
                    switch ($type) {
                        case 'arrayToCommaList':
                            $metaData[$mapping['property']] = implode(',', $mediaProperties[$key]['values']);

                            break;
                        default:
                            $metaData[$mapping['property']] = $mediaProperties[$key]['value'];

                            break;
                    }
                }
            }
        }

        return $metaData;
    }

    /**
     * Generate filename from qbank media and image object.
     *
     * @param string $qbankFilename
     * @param int $qbankMediaId
     * @param string $fileExtension
     * @param string $imageTemplate
     * @return string
     */
    public static function generateFileName($qbankFilename, $qbankMediaId, $fileExtension, $imageTemplate)
    {
        $checkSum = hash('crc32', $qbankMediaId . $imageTemplate);
        $basicFileUtility = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Utility\File\BasicFileUtility::class);
        $newFilename = self::getCleanFilename($qbankFilename) . '_' . $checkSum . '.' . $fileExtension;
        $newFilename = $basicFileUtility->cleanFileName($newFilename);

        return $newFilename;
    }

    public static function getCleanFilename($filename)
    {
        $pathInfo = pathinfo($filename);

        return $pathInfo['filename'];
    }

    public static function encodeMediaId($deploymentSiteId, $poster, $mp4, $ogg, $webm, $title, $author)
    {
        $encodedString .= $deploymentSiteId;
        $encodedString .= '|';
        $encodedString .= $poster;
        $encodedString .= '|';
        $encodedString .= $mp4;
        $encodedString .= '|';
        $encodedString .= $ogg;
        $encodedString .= '|';
        $encodedString .= $webm;
        $encodedString .= '|';
        $encodedString .= $title;
        $encodedString .= '|';
        $encodedString .= $author;

        return $encodedString;
    }

    public static function decodeMediaId($mediaId)
    {
        [$deploymentSiteId, $poster, $mp4, $ogg, $webm, $title, $author] = GeneralUtility::trimExplode('|', $mediaId);

        return [
            'deploymentSiteId' => $deploymentSiteId,
            'poster' => $poster,
            'mp4' => $mp4,
            'ogg' => $ogg,
            'webm' => $webm,
            'title' => $title,
            'author' => $author,
        ];
    }

    public static function getPublicUrl($mediaId, $type)
    {
        $decodedMediaId = self::decodeMediaId($mediaId);
        if ((int)$decodedMediaId['deploymentSiteId'] > 0
            && strlen($decodedMediaId[$type]) > 0
        ) {
            $settingPublicUrl = self::getSettingPublicUrl($decodedMediaId['deploymentSiteId']);
            if ($settingPublicUrl) {
                $publicUrl = rtrim($settingPublicUrl, '/');
                $publicUrl .= '/' . $decodedMediaId[$type];
            }
            if (GeneralUtility::isValidUrl($publicUrl)) {
                return $publicUrl;
            }
        }

        return false;
    }

    public static function getConfiguration()
    {
        $objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
        $configurationManager = $objectManager->get(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class);
        $typoScriptSetupFull = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        if ($typoScriptSetupFull['module.']['tx_qbank_file_qbankmedia.']['settings.']) {
            $typoScript = GeneralUtility::removeDotsFromTS($typoScriptSetupFull['module.']['tx_qbank_file_qbankmedia.']['settings.']);
        }

        return $typoScript;
    }

    /**
     * Get public Url for QBank deploymentSiteId from configuration.
     *
     * @param int $deploymentSiteId The QBank id of the deployment site
     *
     * @return string
     */
    public static function getSettingPublicUrl($deploymentSiteId)
    {
        $settings = self::getConfiguration();

        return $settings['mappings']['publishPlaces'][$deploymentSiteId]['publicUrl'];
    }

    /**
     * Get target folder identifier, where to store media.
     *
     * @return string
     */
    public static function getSettingTargetFolderIdentifier()
    {
        $settings = self::getConfiguration();

        return $settings['targetFolderIdentifier'];
    }

    /**
     * Get target folder identifier, where to store media.
     *
     * @return string
     */
    public static function getSettingMappings()
    {
        $settings = self::getConfiguration();

        return $settings['mappings'];
    }

    /**
     * Get deploymentSites configuration to use in js call.
     *
     * @return string
     */
    public static function getSettingDeploymentSites()
    {
        $settings = self::getConfiguration();

        return $settings['deploymentSite'];
    }

    /**
     * Get deploymentSites configuration to use in js call.
     *
     * @return string
     */
    public static function getSettingQbankRenderer()
    {
        $settings = self::getConfiguration();

        return $settings['qbankRenderer'];
    }

    /**
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    public static function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
