<?php

namespace Pixelant\Qbank\OnlineMedia\Helpers;

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

use Pixelant\Qbank\Utility\QbankUtility;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\AbstractOEmbedHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Vimeo helper class.
 */
class QbankHelper extends AbstractOEmbedHelper
{
    /**
     * Get public url
     * Return NULL if you want to use core default behaviour.
     *
     * @param File $file
     * @param bool $relativeToCurrentScript
     * @return string|null
     */
    public function getPublicUrl(File $file, $relativeToCurrentScript = false)
    {
        $mediaId = $this->getOnlineMediaId($file);

        $remoteUrl = QbankUtility::getPublicUrl($mediaId, 'mp4');
        if (!$remoteUrl) {
            $remoteUrl = QbankUtility::getPublicUrl($mediaId, 'ogg');
        }
        if (!$remoteUrl) {
            $remoteUrl = QbankUtility::getPublicUrl($mediaId, 'webm');
        }
        if (!$remoteUrl) {
            $remoteUrl = QbankUtility::getPublicUrl($mediaId, 'poster');
        }

        return $remoteUrl;
    }

    /**
     * Get local absolute file path to preview image.
     *
     * @param File $file
     * @return string
     */
    public function getPreviewImage(File $file)
    {
        $videoId = $this->getOnlineMediaId($file);
        $temporaryFileName = $this->getTempFolderPath() . 'qbank_' . md5($videoId) . '.jpg';
        if (!file_exists($temporaryFileName)) {
            $oEmbedData = $this->getOEmbedData($videoId);
            $previewImage = GeneralUtility::getUrl($oEmbedData['thumbnail_url']);
            if ($previewImage !== false) {
                file_put_contents($temporaryFileName, $previewImage);
                GeneralUtility::fixPermissions($temporaryFileName);
            }
        }

        return $temporaryFileName;
    }

    /**
     * Try to transform given URL to a File.
     *
     * @param string $url
     * @param Folder $targetFolder
     * @return File|null
     */
    public function transformUrlToFile($url, Folder $targetFolder)
    {
        $mediaId = null;
        $decodedMediaId = QbankUtility::decodeMediaId($url);
        if ((int)$decodedMediaId['deploymentSiteId'] > 0) {
            if (strlen($decodedMediaId['mp4']) > 0
                || strlen($decodedMediaId['ogg']) > 0
                || strlen($decodedMediaId['webm']) > 0
            ) {
                $mediaId = $url;
            }
        }
        if (empty($mediaId)) {
            return null;
        }

        return $this->transformMediaIdToFile($mediaId, $targetFolder, $this->extension);
    }

    /**
     * Get OEmbed data.
     *
     * @param string $mediaId
     * @return array|null
     */
    protected function getOEmbedData($mediaId)
    {
        $oEmbed = [];
        $decodedMediaId = QbankUtility::decodeMediaId($mediaId);

        if ($decodedMediaId['title']) {
            $oEmbed['title'] = $decodedMediaId['title'];
        }
        if ($decodedMediaId['poster']) {
            $oEmbed['thumbnail_url'] = QbankUtility::getPublicUrl($mediaId, 'poster');
        }

        return $oEmbed;
    }

    /**
     * Get oEmbed data url.
     *
     * @param string $mediaId
     * @param string $format
     * @return string
     */
    protected function getOEmbedUrl($mediaId, $format = 'json')
    {
        return false;
    }
}
