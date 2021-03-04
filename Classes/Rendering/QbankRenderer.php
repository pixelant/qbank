<?php

namespace Pixelant\Qbank\Rendering;

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
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperInterface;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Core\Resource\Rendering\FileRendererInterface;

/**
 * Vimeo renderer class.
 */
class QbankRenderer implements FileRendererInterface
{
    /**
     * @var OnlineMediaHelperInterface
     */
    protected $onlineMediaHelper;

    /**
     * Returns the priority of the renderer
     * This way it is possible to define/overrule a renderer
     * for a specific file type/context.
     * For example create a video renderer for a certain storage/driver type.
     * Should be between 1 and 100, 100 is more important than 1.
     *
     * @return int
     */
    public function getPriority()
    {
        return 1;
    }

    /**
     * Check if given File(Reference) can be rendered.
     *
     * @param FileInterface $file File of FileReference to render
     * @return bool
     */
    public function canRender(FileInterface $file)
    {
        return ($file->getMimeType() === 'video/qbank' || $file->getExtension() === 'qbank') && $this->getOnlineMediaHelper($file) !== false;
    }

    /**
     * Get online media helper.
     *
     * @param FileInterface $file
     * @return bool|OnlineMediaHelperInterface
     */
    protected function getOnlineMediaHelper(FileInterface $file)
    {
        if ($this->onlineMediaHelper === null) {
            $orgFile = $file;
            if ($orgFile instanceof FileReference) {
                $orgFile = $orgFile->getOriginalFile();
            }
            if ($orgFile instanceof File) {
                $this->onlineMediaHelper = OnlineMediaHelperRegistry::getInstance()->getOnlineMediaHelper($orgFile);
            } else {
                $this->onlineMediaHelper = false;
            }
        }

        return $this->onlineMediaHelper;
    }

    /**
     * Render for given File(Reference) html output.
     *
     * @param FileInterface $file
     * @param int|string $width TYPO3 known format; examples: 220, 200m or 200c
     * @param int|string $height TYPO3 known format; examples: 220, 200m or 200c
     * @param array $options
     * @param bool $usedPathsRelativeToCurrentScript See $file->getPublicUrl()
     * @return string
     */
    public function render(FileInterface $file, $width, $height, array $options = null, $usedPathsRelativeToCurrentScript = false)
    {
        if ($file instanceof FileReference) {
            $orgFile = $file->getOriginalFile();
        } else {
            $orgFile = $file;
        }

        $content = '';
        $posterAttribute = '';
        $widthAttribute = '';
        $heightAttribute = '';
        $classAttribute = '';
        $styleAttribute = '';
        $mediaId = $this->getOnlineMediaHelper($file)->getOnlineMediaId($orgFile);
        $mp4Url = QbankUtility::getPublicUrl($mediaId, 'mp4');
        $oggUrl = QbankUtility::getPublicUrl($mediaId, 'ogg');
        $webmUrl = QbankUtility::getPublicUrl($mediaId, 'ogg');
        $posterUrl = QbankUtility::getPublicUrl($mediaId, 'poster');
        $qbankRenderSettings = QbankUtility::getSettingQbankRenderer();

        if ($posterUrl) {
            $posterAttribute = ' poster="' . $posterUrl . '"';
        }
        if ((int)$width > 0) {
            $widthAttribute = ' width="' . $width . '"';
        }
        if ((int)$height > 0) {
            $heightAttribute = ' height="' . $height . '"';
        }
        if (isset($qbankRenderSettings['class']) && strlen(trim($qbankRenderSettings['class'])) > 0) {
            $classAttribute = ' class="' . $qbankRenderSettings['class'] . '"';
        }
        if (isset($qbankRenderSettings['style']) && strlen(trim($qbankRenderSettings['style'])) > 0) {
            $styleAttribute = ' style="' . $qbankRenderSettings['style'] . '"';
        }

        if ($mp4Url || $oggUrl || $webmUrl) {
            $content = '<video controls' . $classAttribute . $styleAttribute . $posterAttribute . $widthAttribute . $heightAttribute . '>';
            if ($webmUrl) {
                $content .= '<source src="' . $webmUrl . '" type="video/webm">';
            }
            if ($oggUrl) {
                $content .= '<source src="' . $oggUrl . '" type="video/ogg">';
            }
            if ($mp4Url) {
                $content .= '<source src="' . $mp4Url . '" type="video/mp4">';
            }
            $content .= QbankUtility::getLabel('Error.BrowserNotSupportVideoTag');
            $content .= '</video>';
        }

        return $content;
    }
}
