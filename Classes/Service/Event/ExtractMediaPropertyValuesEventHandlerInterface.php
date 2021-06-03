<?php
declare(strict_types=1);

namespace Pixelant\Qbank\Service\Event;

use Pixelant\Qbank\Domain\Model\Qbank\MediaPropertyValue;

/**
 * Extractors for extracting property values from a QBank MediaResponse object.
 */
interface ExtractMediaPropertyValuesEventHandlerInterface
{
    /**
     * Uses $event->addValues() or $event->addValue() to add new MediaPropertyValue objects.
     *
     * @see MediaPropertyValue
     *
     * @param ExtractMediaPropertyValuesEvent $event
     */
    public function __invoke(ExtractMediaPropertyValuesEvent $event): void;
}
