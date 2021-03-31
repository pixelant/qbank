<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Service\Event;

/**
 * Interface for handling events to provide record title.
 */
interface ResolvePageTitleEventHandlerInterface
{
    /**
     * Handle the event and (if appropriate) use $this->setTitle() to set the record title.
     *
     * @param ResolvePageTitleEvent $event
     */
    public function __invoke(ResolvePageTitleEvent $event): void;
}
