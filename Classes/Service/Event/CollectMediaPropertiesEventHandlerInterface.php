<?php
declare(strict_types=1);

namespace Pixelant\Qbank\Service\Event;

use Pixelant\Qbank\Domain\Model\Qbank\MediaProperty;

/**
 * Event handler that helps collect MediaProperty objects.
 */
interface CollectMediaPropertiesEventHandlerInterface
{
    /**
     * Uses $event->addMediaProperties() or $event->addMediaProperty() to add new MediaProperty objects. Each
     * object should ideally be a sub class of MediaProperty.
     *
     * @see MediaProperty
     *
     * @param CollectMediaPropertiesEvent $event
     */
    public function __invoke(CollectMediaPropertiesEvent $event): void;
}
