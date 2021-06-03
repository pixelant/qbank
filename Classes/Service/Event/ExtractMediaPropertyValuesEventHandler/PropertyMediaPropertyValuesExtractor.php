<?php
declare(strict_types=1);

namespace Pixelant\Qbank\Service\Event\ExtractMediaPropertyValuesEventHandler;

use Pixelant\Qbank\Domain\Model\Qbank\MediaPropertyValue;
use Pixelant\Qbank\Service\Event\CollectMediaPropertiesEventHandler\BaseMediaPropertiesCollector;
use Pixelant\Qbank\Service\Event\CollectMediaPropertiesEventHandler\PropertyMediaPropertiesCollector;
use Pixelant\Qbank\Service\Event\ExtractMediaPropertyValuesEvent;
use Pixelant\Qbank\Service\Event\ExtractMediaPropertyValuesEventHandlerInterface;

/**
 * Extracts base properties of a Qbank media object, such as "uploaded" or "name".
 */
class PropertyMediaPropertyValuesExtractor implements ExtractMediaPropertyValuesEventHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ExtractMediaPropertyValuesEvent $event): void
    {
        $propertyValues = [];
        foreach ($event->getMediaResponse()->getPropertySets() as $propertySet) {
            foreach ($propertySet->getProperties() as $property) {
                $systemName = $property->getPropertyType()->getSystemName();

                // We assume a property repeated in multiple property shares the same value.
                if (isset($propertyValues[$systemName])) {
                    continue;
                }

                // Some properties have multiple values. We put them into an array.
                if (isset($propertyValues[$systemName]) && is_array($propertyValues[$systemName])) {
                    $propertyValues[$systemName] = array_merge(
                        $propertyValues[$systemName],
                        [$property->getValue()]
                    );
                } elseif (isset($propertyValues[$systemName])) {
                    $propertyValues[$systemName] = [
                        $propertyValues[$systemName],
                        $property->getValue()
                    ];
                } else {
                    $propertyValues[$systemName] = $property->getValue();
                }
            }
        }

        $values = [];
        foreach (PropertyMediaPropertiesCollector::getProperties() as $property) {
            $value = $propertyValues[$property->getKeyWithoutPrefix()] ?? '';

            $values[] = new MediaPropertyValue($value, $property);
        }

        $event->addValues($values);
    }
}
