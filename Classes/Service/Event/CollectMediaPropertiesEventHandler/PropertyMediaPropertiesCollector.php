<?php
declare(strict_types=1);

namespace Pixelant\Qbank\Service\Event\CollectMediaPropertiesEventHandler;

use Pixelant\Qbank\Domain\Model\Qbank\MediaProperty;
use Pixelant\Qbank\Domain\Model\Qbank\PropertyMediaProperty;
use Pixelant\Qbank\Repository\PropertyTypeRepository;
use Pixelant\Qbank\Service\Event\CollectMediaPropertiesEvent;
use Pixelant\Qbank\Service\Event\CollectMediaPropertiesEventHandlerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Collects MediaProperty object from the property sets.
 */
class PropertyMediaPropertiesCollector implements CollectMediaPropertiesEventHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(CollectMediaPropertiesEvent $event): void
    {
        $event->addMediaProperties(self::getProperties());
    }

    /**
     * Generates and returns the MediaProperty objects.
     *
     * @return PropertyMediaProperty[]
     */
    public static function getProperties(): array
    {
        /** @var PropertyTypeRepository $propertyTypeRepository */
        $propertyTypeRepository = GeneralUtility::makeInstance(PropertyTypeRepository::class);

        $propertyTypes = $propertyTypeRepository->findAll();

        $mediaProperties = [];
        foreach ($propertyTypes as $propertyType) {
            $mediaProperties[] = new PropertyMediaProperty(
                $propertyType->getDataTypeId(),
                $propertyType->getSystemName(),
                $propertyType->getName()
            );
        }

        return $mediaProperties;
    }
}
