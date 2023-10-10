<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Service\Event\CollectMediaPropertiesEventHandler;

use Pixelant\Qbank\Service\Event\CollectMediaPropertiesEventHandlerInterface;
use Pixelant\Qbank\Domain\Model\Qbank\BaseMediaProperty;
use Pixelant\Qbank\Domain\Model\Qbank\MediaProperty;
use Pixelant\Qbank\Service\Event\CollectMediaPropertiesEvent;
use QBNK\QBank\API\Model\PropertyType;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Adds base properties of a Qbank media object, such as "uploaded" or "name".
 */
class BaseMediaPropertiesCollector implements CollectMediaPropertiesEventHandlerInterface
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
                LocalizationUtility::translate($ll . 'name', 'qbank')
            ),
            new BaseMediaProperty(
                PropertyType::DATATYPE_STRING,
                'filename',
                LocalizationUtility::translate($ll . 'filename', 'qbank')
            ),
            new BaseMediaProperty(
                PropertyType::DATATYPE_DATETIME,
                'created',
                LocalizationUtility::translate($ll . 'created', 'qbank')
            ),
            new BaseMediaProperty(
                PropertyType::DATATYPE_DATETIME,
                'updated',
                LocalizationUtility::translate($ll . 'updated', 'qbank')
            ),
            new BaseMediaProperty(
                PropertyType::DATATYPE_DATETIME,
                'uploaded',
                LocalizationUtility::translate($ll . 'uploaded', 'qbank')
            ),
            new BaseMediaProperty(
                PropertyType::DATATYPE_INTEGER,
                'rating',
                LocalizationUtility::translate($ll . 'rating', 'qbank')
            ),
        ];
    }
}
