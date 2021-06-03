<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Domain\Model\Qbank;

/**
 * Represents a base property of a Qbank media object, such as "objectId" or "name".
 */
class BaseMediaProperty extends MediaProperty
{
    public const ORIGIN_PREFIX = 'base';
}
