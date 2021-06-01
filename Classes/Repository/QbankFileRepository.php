<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class QbankFileRepository
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
        $selectFields = [
            'sys_file.uid as sys_file_uid',
            'sys_file.name as sys_file_name',
            'sys_file.storage as sys_file_storage',
            'sys_file.identifier as sys_file_identifier',
            'sys_file.tx_qbank_id as sys_file_tx_qbank_id',
            'sys_file.tx_qbank_file_timestamp as sys_file_tx_qbank_file_timestamp',
            'sys_file.tx_qbank_metadata_timestamp as sys_file_tx_qbank_metadata_timestamp',
            'sys_file_metadata.uid as sys_file_metadata_uid',
        ];

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_file');

        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $queryBuilder
            ->select(...$selectFields)
            ->from('sys_file')
            ->join(
                'sys_file',
                'sys_file_metadata',
                'sys_file_metadata',
                $queryBuilder->expr()->eq(
                    'sys_file_metadata.file',
                    $queryBuilder->quoteIdentifier('sys_file.uid')
                )
            )
            ->where(
                $queryBuilder->expr()->neq(
                    'sys_file.tx_qbank_id',
                    $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                )
            )
            ->orderBy('sys_file.tx_qbank_file_timestamp')
            ->addOrderBy('sys_file.modification_date');

        return $queryBuilder;
    }
}
