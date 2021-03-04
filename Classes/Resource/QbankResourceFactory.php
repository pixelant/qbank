<?php

namespace Pixelant\Qbank\Resource;

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

/**
 * Factory class for FAL objects.
 */
class QbankResourceFactory extends \TYPO3\CMS\Core\Resource\ResourceFactory
{
    /**
     * Creates an instance of a FileReference object. The $fileReferenceData can
     * be supplied to increase performance.
     *
     * @param int $uid The uid of the file usage (sys_file_reference) to instantiate.
     * @param array $fileReferenceData The record row from database.
     * @param bool $raw Whether to get raw results without performing overlays
     * @return FileReference
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException
     */
    public function getFileReferenceObject($uid, array $fileReferenceData = [], $raw = false)
    {
        // exec parent
        parent::getFileReferenceObject($uid, $fileReferenceData, $raw);

        try {
            self::checkQbankUsage($uid);
        } catch (\Exception $e) {
        } catch (\Throwable $e) {
            // Just continue render
        }

        return $this->fileReferenceInstances[$uid];
    }

    /**
     * Checks if fileReferenceObject is from QBank
     * and if it is not reported as used is will be inserted to cache
     * and a scheduler task can do the api call to register image as used.
     *
     * @param int $uid The uid of the file usage (sys_file_reference) to instantiate.
     *
     * @return void
     */
    protected function checkQbankUsage($uid): void
    {
        // check if QBank propertirs exists
        if ($this->fileReferenceInstances[$uid]->getOriginalFile()->hasProperty('qbank_id')
            && $this->fileReferenceInstances[$uid]->hasProperty('qbank_reported')
            && $GLOBALS['BE_USER'] === null
        ) {
            // check if object have qbank_id > 0 and isn't reported
            $sysFileReferenceUid = (int)$this->fileReferenceInstances[$uid]->getProperty('uid');
            $qbankMediaId = (int)$this->fileReferenceInstances[$uid]->getOriginalFile()->getProperty('qbank_id');
            $qbankIsReported = (int)$this->fileReferenceInstances[$uid]->getProperty('qbank_reported');
            if ($qbankMediaId > 0 && $qbankIsReported < 1) {
                $cache = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager')->getCache('qbank_connector');
                $cacheKey = 'fileReferencesToReportAsUsed';
                $cacheContent = '';
                // Get previous cache hash
                if ($cache->has($cacheKey)) {
                    $cacheContent = json_decode($cache->get($cacheKey), true);
                }
                // if not set or differs set cache
                if ($cacheContent === '' || !isset($cacheContent[$sysFileReferenceUid])) {
                    $url = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
                    $languageAspect = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class)->getAspect('language');
                    $sysLanguageUid = $languageAspect->getId();
                    $title = $GLOBALS['TSFE']->page['title'];
                    $pid = $GLOBALS['TSFE']->page['uid'];

                    $cacheContent[$sysFileReferenceUid] = [
                        'url' => $url,
                        'title' => $title,
                        'sys_language_uid' => $sysLanguageUid,
                        'pid' => $pid,
                    ];
                    $cache->set($cacheKey, json_encode($cacheContent));
                }
            }
        }
    }
}
