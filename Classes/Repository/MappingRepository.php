<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MappingRepository
{
    /**
     * Find all.
     *
     * @return void
     */
    public function findAll()
    {
        return $this->getQueryBuilder()
            ->execute()
            ->fetchAllAssociative();
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_qbank_domain_model_mapping');
        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder
            ->select('*')
            ->from('tx_qbank_domain_model_mapping')
            ->orderBy('source_property')
            ->addOrderBy('target_property');

        return $queryBuilder;
    }
}
