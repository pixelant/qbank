<?php

declare(strict_types=1);

namespace Pixelant\Qbank\Repository;

use Doctrine\DBAL\FetchMode;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SysFileReferenceRepository
{
    public const TABLE_NAME = 'sys_file_reference';

    /**
     * Fetch sys_file_reference records for a given FAL file id.
     *
     * @param int $fileId
     * @return array|null
     */
    public function fetchRawSysFileReferencesByFileId($fileId): ?array
    {
        $queryBuilder = $this->getQueryBuilder();

        $fileReferenceData = $queryBuilder->select('*')
                ->from('sys_file_reference')
                ->where(
                    $queryBuilder->expr()->eq(
                        'uid_local',
                        $queryBuilder->createNamedParameter($fileId, \PDO::PARAM_INT)
                    )
                )
                ->andWhere(
                    $queryBuilder->expr()->eq(
                        'table_local',
                        $queryBuilder->createNamedParameter('sys_file', \PDO::PARAM_STR)
                    )
                )
                ->execute();

        if (!method_exists($fileReferenceData, 'fetchAllAssociative')) {
            return $fileReferenceData->fetchAll(FetchMode::ASSOCIATIVE);
        }

        return $fileReferenceData->fetchAllAssociative();
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

        return $queryBuilder;
    }
}
