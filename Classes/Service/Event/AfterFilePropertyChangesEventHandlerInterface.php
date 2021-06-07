<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Service\Event;

/**
 * Handles events that happen after file properties have been changed.
 */
interface AfterFilePropertyChangesEventHandlerInterface
{
    /**
     * Use $event->getFile() to access the file object.
     *
     * @param AfterFilePropertyChangesEvent $event
     */
    public function __invoke(AfterFilePropertyChangesEvent $event): void;
}
