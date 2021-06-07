<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Service\Event\AfterFilePropertyChangesEventHandler;

use Pixelant\Qbank\Service\Event\AfterFilePropertyChangesEvent;
use Pixelant\Qbank\Service\Event\FilePropertyChangeEventHandler\MetaDataFilePropertyChangeEventHandler;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PersistMetaDataChanges implements \Pixelant\Qbank\Service\Event\AfterFilePropertyChangesEventHandlerInterface
{
    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function __invoke(AfterFilePropertyChangesEvent $event): void
    {
        $updatedProperties = MetaDataFilePropertyChangeEventHandler::getUpdatedProperties();
        MetaDataFilePropertyChangeEventHandler::resetUpdatedProperties();

        $data = [
            'sys_file_metadata' => [
                (string)$event->getFile()->getMetaData()->offsetGet('uid') => $updatedProperties,
            ],
        ];

        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);

        $dataHandler->start($data, []);
        $dataHandler->process_datamap();

        if (count($dataHandler->errorLog) > 0) {
            // TODO: Use custom exception class.
            throw new \Exception(
                'Errors found in DataHandler error log: ' . implode(',', $dataHandler->errorLog),
                1622744599
            );
        }
    }
}
