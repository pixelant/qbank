<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Service\Event;


use TYPO3\CMS\Core\Resource\File;

/**
 * This event is called after file properties have been changed.
 */
class AfterFilePropertyChangesEvent
{
    /**
     * @var File
     */
    protected $file;

    /**
     * FilePropertyChangeEvent constructor.
     * @param File $file
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * @return File
     */
    public function getFile(): File
    {
        return $this->file;
    }
}
