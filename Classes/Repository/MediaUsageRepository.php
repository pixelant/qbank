<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Repository;

use Pixelant\Qbank\Utility\QbankUtility;
use QBNK\QBank\API\Model\MediaUsage;
use QBNK\QBank\API\Model\MediaUsageResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

class MediaUsageRepository extends AbstractRepository
{
    /**
     * Add media usage.
     *
     * @param MediaUsage $mediaUsage
     */
    public function add(MediaUsage $mediaUsage): void
    {
        $sessionSourceId = $this->getSessionSourceId();

        if (!$sessionSourceId) {
            return;
        }

        $remoteAddress = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        if (empty($remoteAddress)) {
            $remoteAddress = '127.0.0.1';
        }

        $userAgent = GeneralUtility::getIndpEnv('HTTP_USER_AGENT');
        if (empty($userAgent)) {
            $userAgent = 'cli';
        }

        $sessionId = $this->api->events()->session(
            $sessionSourceId,
            'typo3_qbank_' . StringUtility::getUniqueId(),
            $remoteAddress,
            $userAgent
        );

        $this->api->events()->addUsage(
            $sessionId,
            $mediaUsage
        );
    }

    /**
     * Remove a single media usage.
     *
     * There might technically be multiple, so we'll remove one and trust the other usages are also removed
     * individually.
     *
     * @param int $qbankId
     * @param string $localId Usually a record <tablename>_<uid>
     */
    public function removeOneByQbankAndLocalId(int $qbankId, string $localId): void
    {
        $usage = $this->findByQbankAndLocalId($qbankId, $localId)[0] ?? false;

        if (!$usage) {
            return;
        }

        $this->api->events()->removeUsage($usage->getId());
    }

    /**
     * Find media usage for a specific media ID and local ID.
     *
     * @param int $qbankId
     * @param string $localId Usually a record <tablename>_<uid>
     * @return MediaUsageResponse[]
     */
    public function findByQbankAndLocalId(int $qbankId, string $localId): array
    {
        $usages = $this->findByQbankId($qbankId);

        $result = [];
        foreach ($usages as $usage) {
            if ($usage->getContext()['localID'] ?? '' === $localId) {
                $result[] = $usage;
            }
        }

        return $result;
    }

    /**
     * Find media usage for a specific media ID.
     *
     * @param int $qbankId
     * @return MediaUsageResponse[]
     */
    public function findByQbankId(int $qbankId): array
    {
        $sessionSourceId = $this->getSessionSourceId();

        if (!$sessionSourceId) {
            return $this->api->media()->listUsages($qbankId);
        }

        return $this->api->media()->listUsages($qbankId, (string)$sessionSourceId);
    }

    /**
     * Get the session source ID.
     *
     * @return int
     */
    protected function getSessionSourceId(): int
    {
        return QbankUtility::getConfigurationManager()->getSessionSource();
    }
}
