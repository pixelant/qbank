<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Service\Event;

interface FilePropertyChangeEventHandler
{
    /**
     * Handle setting a File object property.
     *
     * @param FilePropertyChangeEvent $event
     */
    public function __invoke(FilePropertyChangeEvent $event): void;
}
