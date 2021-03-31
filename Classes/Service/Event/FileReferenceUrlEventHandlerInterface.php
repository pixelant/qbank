<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Service\Event;

/**
 * Interface for handling events to provide a public URL to a page where QBank media is used.
 */
interface FileReferenceUrlEventHandlerInterface
{
    /**
     * Handle the event and (if appropriate) use $this->setUrl() to set the URL.
     *
     * @param FileReferenceUrlEvent $event
     */
    public function __invoke(FileReferenceUrlEvent $event): void;
}
