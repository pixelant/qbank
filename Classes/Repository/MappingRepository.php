<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Repository;

use Doctrine\DBAL\FetchMode;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MappingRepository
{
    const TABLE_NAME = 'tx_qbank_domain_model_mapping';

    /**
     * Find all.
     *
     * @return array
     */
    public function findAll(): array
    {
        $resultStatement = $this->getQueryBuilder()->execute();

        if (!method_exists($resultStatement, 'fetchAllAssociative')) {
            return $resultStatement->fetchAll(FetchMode::ASSOCIATIVE);
        }

        return $resultStatement->fetchAllAssociative();
    }

    /**
     * Returns all mappings as an associative array of `<source_property> => <target_property>` pairs.
     *
     * @return array
     */
    public function findAllAsKeyValuePairs(): array
    {
        $rows = $this->findAll();

        $pairs = [];
        foreach ($rows as $row) {
            $pairs[$row['source_property']] = $row['target_property'];
        }

        return $pairs;
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->orderBy('source_property')
            ->addOrderBy('target_property');

        return $queryBuilder;
    }
}
