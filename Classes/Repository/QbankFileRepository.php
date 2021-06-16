<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Repository;

use Doctrine\DBAL\FetchMode;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class QbankFileRepository
{
    /**
     * Find all QBank files.
     *
     * @return array
     */
    public function findAll(): array
    {
        $selectFields = [
            'sys_file.uid',
            'sys_file.name',
            'sys_file.storage',
            'sys_file.identifier',
            'sys_file.tx_qbank_id',
            'sys_file.tx_qbank_file_timestamp',
            'sys_file.tx_qbank_metadata_timestamp',
            'sys_file.tx_qbank_remote_change_timestamp',
            'sys_file.tx_qbank_status_updated_timestamp',
            'sys_file.tx_qbank_remote_replaced_by',
            'sys_file.tx_qbank_remote_is_replaced',
            'sys_file_metadata.uid AS metadata_uid',
        ];

        $queryBuilder = $this->getQueryBuilder();

        $resultStatement = $queryBuilder
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
                $queryBuilder->expr()->gt(
                    'sys_file.tx_qbank_id',
                    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                )
            )
            ->orderBy('sys_file.tx_qbank_file_timestamp')
            ->addOrderBy('sys_file.modification_date')
            ->execute();

        if (!method_exists($resultStatement, 'fetchAllAssociative')) {
            return $resultStatement->fetchAll(FetchMode::ASSOCIATIVE);
        }

        return $resultStatement->fetchAllAssociative();
    }

    /**
     * Fetch list of files status should be update for.
     *
     * @param int $limit
     * @param int $interval
     * @return array
     */
    public function fetchStatusUpdateQueue(int $limit, int $interval): array
    {
        $queryBuilder = $this->getQueryBuilder();

        $resultStatement = $queryBuilder
            ->select('*')
            ->from('sys_file')
            ->where(
                $queryBuilder->expr()->gt(
                    'sys_file.tx_qbank_id',
                    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                )
            )
            ->andWhere(
                $queryBuilder->expr()->lt(
                    'sys_file.tx_qbank_status_updated_timestamp',
                    $queryBuilder->createNamedParameter(time() - $interval, \PDO::PARAM_INT)
                )
            )
            ->setMaxResults($limit)
            ->orderBy('sys_file.tx_qbank_status_updated_timestamp')
            ->execute();

        if (!method_exists($resultStatement, 'fetchAllAssociative')) {
            return $resultStatement->fetchAll(FetchMode::ASSOCIATIVE);
        }

        return $resultStatement->fetchAllAssociative();
    }

    /**
     * Fetch list of files where metadata or file needs to be updated.
     *
     * @param int $limit
     * @return array
     */
    public function fetchFilesToUpdate(int $limit): array
    {
        $queryBuilder = $this->getQueryBuilder();

        $resultStatement = $queryBuilder
            ->select('*')
            ->from('sys_file')
            ->where(
                $queryBuilder->expr()->gt(
                    'sys_file.tx_qbank_id',
                    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                )
            )
            ->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->gt(
                        'tx_qbank_remote_change_timestamp',
                        'tx_qbank_metadata_timestamp'
                    ),
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->gt(
                            'tx_qbank_remote_replaced_by',
                            $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                        ),
                        $queryBuilder->expr()->eq(
                            'tx_qbank_remote_is_replaced',
                            $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                        )
                    )
                )
            )
            ->setMaxResults($limit)
            ->orderBy('sys_file.tx_qbank_status_updated_timestamp')
            ->execute();

        if (!method_exists($resultStatement, 'fetchAllAssociative')) {
            return $resultStatement->fetchAll(FetchMode::ASSOCIATIVE);
        }

        return $resultStatement->fetchAllAssociative();
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_file');

        $queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder;
    }
}
