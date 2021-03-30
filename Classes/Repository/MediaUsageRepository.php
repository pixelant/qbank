<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Repository;


use Pixelant\Qbank\Utility\QbankUtility;
use QBNK\QBank\API\Model\MediaUsage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

class MediaUsageRepository extends AbstractRepository
{
    /**
     * Add media usage.
     *
     * @param MediaUsage $mediaUsage
     */
    public function add(MediaUsage $mediaUsage)
    {
        $sessionSourceId = QbankUtility::getConfigurationManager()->getSessionSource();

        if (!$sessionSourceId) {
            return;
        }

        $sessionId = $this->api->events()->session(
            $sessionSourceId,
            'typo3_qbank_' . StringUtility::getUniqueId(),
            GeneralUtility::getIndpEnv('REMOTE_ADDR'),
            GeneralUtility::getIndpEnv('HTTP_USER_AGENT')
        );

        $this->api->events()->addUsage(
            $sessionId,
            $mediaUsage
        );
    }
}
