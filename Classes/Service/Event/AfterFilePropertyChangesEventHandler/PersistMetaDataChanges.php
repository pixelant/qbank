<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Service\Event\AfterFilePropertyChangesEventHandler;

use Pixelant\Qbank\Exception\PersistMetaDataChangesException;
use Pixelant\Qbank\Service\Event\AfterFilePropertyChangesEvent;
use Pixelant\Qbank\Service\Event\FilePropertyChangeEventHandler\MetaDataFilePropertyChangeEventHandler;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/*
 * Persist the changes that have previously been made in the MetaDatafilePropertyChangeEventHandler.
 */
class PersistMetaDataChanges implements \Pixelant\Qbank\Service\Event\AfterFilePropertyChangesEventHandlerInterface
{
    /**
     * {@inheritdoc}
     * @throws PersistMetaDataChangesException
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
            throw new PersistMetaDataChangesException(
                'Errors found in DataHandler error log: ' . implode(',', $dataHandler->errorLog),
                1622744599
            );
        }
    }
}
