<?php
declare(strict_types=1);

namespace Pixelant\Qbank\Service\Event\ExtractMediaPropertyValuesEventHandler;

use Pixelant\Qbank\Domain\Model\Qbank\MediaPropertyValue;
use Pixelant\Qbank\Service\Event\CollectMediaPropertiesEventHandler\BaseMediaPropertiesCollector;
use Pixelant\Qbank\Service\Event\ExtractMediaPropertyValuesEvent;
use Pixelant\Qbank\Service\Event\ExtractMediaPropertyValuesEventHandlerInterface;

/**
 * Extracts base properties of a Qbank media object, such as "uploaded" or "name".
 */
class BaseMediaPropertyValuesExtractor implements ExtractMediaPropertyValuesEventHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ExtractMediaPropertyValuesEvent $event): void
    {
        $values = [];
        foreach (BaseMediaPropertiesCollector::getProperties() as $property) {
            $getterMethodName = 'get' . ucfirst($property->getKeyWithoutPrefix());

            $value = $event->getMediaResponse()->$getterMethodName();

            $values[] = new MediaPropertyValue($value, $property);
        }

        $event->addValues($values);
    }
}
