<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Repository;

use QBNK\QBank\API\Model\PropertyType;

class PropertyTypeRepository extends AbstractRepository
{
    /**
     * @var array<PropertyType>
     */
    protected static $propertyTypeCache = null;

    /**
     * Initialize PropertyTypeCache and store them with SystemName as key.
     *
     * @return void
     */
    protected function initializePropertyTypeCache(): void
    {
        $propertyTypes = $this->api->propertysets()->listPropertyTypes();
        foreach ($propertyTypes as $propertyType) {
            self::$propertyTypeCache[$propertyType->getSystemName()] = $propertyType;
        }
    }

    /**
     * Retrieve all ProperyTypes.
     *
     * @return PropertyType[]
     */
    public function findAll(): array
    {
        if (!isset(self::$propertyTypeCache)) {
            $this->initializePropertyTypeCache();
        }

        return self::$propertyTypeCache;
    }

    /**
     * Retrieve ProperyTypes by SystemName.
     *
     * @param string $systemName
     * @return PropertyType|null
     */
    public function findBySystemName(string $systemName): ?PropertyType
    {
        return self::$propertyTypeCache[$systemName];
    }
}
