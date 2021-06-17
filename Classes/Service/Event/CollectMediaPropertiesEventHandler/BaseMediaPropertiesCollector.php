<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Service\Event\CollectMediaPropertiesEventHandler;

use Pixelant\Qbank\Domain\Model\Qbank\BaseMediaProperty;
use Pixelant\Qbank\Domain\Model\Qbank\MediaProperty;
use Pixelant\Qbank\Service\Event\CollectMediaPropertiesEvent;
use QBNK\QBank\API\Model\PropertyType;

/**
 * Adds base properties of a Qbank media object, such as "uploaded" or "name".
 */
class BaseMediaPropertiesCollector implements \Pixelant\Qbank\Service\Event\CollectMediaPropertiesEventHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(CollectMediaPropertiesEvent $event): void
    {
        $event->addMediaProperties(self::getProperties());
    }

    /**
     * Generates and returns the MediaProperty objects.
     *
     * @return BaseMediaProperty[]
     */
    public static function getProperties(): array
    {
        $ll = 'LLL:EXT:qbank/Resources/Private/Language/locallang_db.xlf:baseProperty.label.';

        return [
            new BaseMediaProperty(
                PropertyType::DATATYPE_STRING,
                'name',
                $ll . 'name'
            ),
            new BaseMediaProperty(
                PropertyType::DATATYPE_STRING,
                'filename',
                $ll . 'filename'
            ),
            new BaseMediaProperty(
                PropertyType::DATATYPE_DATETIME,
                'created',
                $ll . 'created'
            ),
            new BaseMediaProperty(
                PropertyType::DATATYPE_DATETIME,
                'updated',
                $ll . 'updated'
            ),
            new BaseMediaProperty(
                PropertyType::DATATYPE_DATETIME,
                'uploaded',
                $ll . 'uploaded'
            ),
            new BaseMediaProperty(
                PropertyType::DATATYPE_INTEGER,
                'rating',
                $ll . 'rating'
            ),
        ];
    }
}
