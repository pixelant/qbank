<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Service\Event;

use Psr\EventDispatcher\StoppableEventInterface;
use TYPO3\CMS\Core\Resource\File;

/**
 * An event that is called when a file property should be changed.
 */
class FilePropertyChangeEvent implements StoppableEventInterface
{
    /**
     * @var bool
     */
    protected $stopPropagation = false;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var string
     */
    protected $propertyName;

    /**
     * @var string|int|float
     */
    protected $propertyValue;

    /**
     * FilePropertyChangeEvent constructor.
     * @param File $file
     * @param string $propertyName
     * @param string|int|float $propertyValue
     */
    public function __construct(File $file, string $propertyName, $propertyValue)
    {
        $this->file = $file;
        $this->propertyName = $propertyName;
        $this->propertyValue = $propertyValue;
    }

    /**
     * @return File
     */
    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * The File object property name to change.
     *
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * The File object property value to change.
     *
     * @return float|int|string
     */
    public function getPropertyValue()
    {
        return $this->propertyValue;
    }

    /**
     * Stops event propagation.
     */
    public function stopPropagation(): void
    {
        $this->stopPropagation = true;
    }

    /**
     * @return bool
     */
    public function isPropagationStopped(): bool
    {
        return $this->stopPropagation;
    }
}
