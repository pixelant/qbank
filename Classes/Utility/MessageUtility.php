<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Utility;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Convenience methods relating to TYPO3 messaging.
 */
class MessageUtility
{
    public static function enqueueMessage(
        string $message,
        string $title = '',
        ContextualFeedbackSeverity $severity = ContextualFeedbackSeverity::OK
    ): void {
        $context = GeneralUtility::makeInstance(Context::class);
        $beUserId = $context->getPropertyFromAspect('backend.user', 'id');

        if ($beUserId > 0) {
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $notificationQueue = $flashMessageService->getMessageQueueByIdentifier(
                FlashMessageQueue::NOTIFICATION_QUEUE
            );
            $flashMessage = GeneralUtility::makeInstance(
                FlashMessage::class,
                $message,
                $title,
                $severity,
                true
            );
            $notificationQueue->enqueue($flashMessage);
        }

        $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        $logger->error($message . PHP_EOL . $title);
    }
}
