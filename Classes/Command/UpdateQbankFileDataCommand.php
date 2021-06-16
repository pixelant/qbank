<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Pixelant\Qbank\Command;

use Pixelant\Qbank\Repository\QbankFileRepository;
use Pixelant\Qbank\Service\QbankService;
use Pixelant\Qbank\Utility\QbankUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Update status on QBank files.
 * Fetches qbank files where tx_qbank_status_updated_timestamp isn't updated within interval.
 * Should then update tx_qbank_remote_change_timestamp and tx_qbank_status_updated_timestamp.
 * Next Command can then fetch qbank files where tx_qbank_remote_change_timestamp is
 * newer than the tx_qbank_file_timestamp or tx_qbank_metadata_timestamp.
 */
class UpdateQbankFileDataCommand extends Command
{
    /**
     * Configure the command by defining the name, options and arguments.
     *
     * @return void
     */
    public function configure(): void
    {
        $this->setDescription('Update metadata and file on downloaded QBank files.')
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Limit number of files to check per run. (default 10)',
                10
            )
            ->addOption(
                'check',
                'c',
                InputOption::VALUE_NONE,
                'Only display information, don\'t update records.'
            );
    }

    /**
     * Executes the command for adding or removing the lock file.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        Bootstrap::initializeBackendAuthentication();
        $io = new SymfonyStyle($input, $output);

        $isTestOnly = $input->getOption('check');
        $limit = (int)$input->getOption('limit');

        /** @var QbankFileRepository $qbankFileRepository */
        $qbankFileRepository = GeneralUtility::makeInstance(QbankFileRepository::class);
        $updateQueue = $qbankFileRepository->fetchFilesToUpdate($limit);

        $io->title('Update QBank file status.');

        if (count($updateQueue) === 0) {
            $io->success('All QBank files are up to date.');

            return 0;
        }

        $io->section('Checking ' . count($updateQueue) . ' files.');

        $autoUpdate = QbankUtility::getAutoUpdateOption();

        foreach ($updateQueue as $file) {
            $message = $this->processFile($file, $autoUpdate, $isTestOnly);
            $io->writeln($message);
        }

        $io->success('Status has been updated for ' . count($updateQueue) . ' QBank files.');

        return 0;
    }

    protected function processFile(array $file, bool $autoUpdate, bool $isTestOnly)
    {
        /** @var QbankService $qbankService */
        $qbankService = GeneralUtility::makeInstance(QbankService::class);

        $action = '';
        $prefix = 'Auto update is disabled';

        // Metadata=1,File=2,Metadata and file=3
        if ($autoUpdate & 1) {
            $prefix = 'Update';
            $action = 'metadata';
            if (!$isTestOnly) {
                $qbankService->synchronizeMetadata($file['uid']);
            }
        }

        if ($autoUpdate & 2) {
            $prefix = 'Update';
            $action = 'file is not replaced';
            if (!$isTestOnly) {
                if ($file['tx_qbank_remote_replaced_by'] > 0) {
                    $action = 'file';
                    $qbankService->replaceLocalMedia($file['uid']);
                }
            }
        }

        if ($autoUpdate & 3) {
            $prefix = 'Update';
            $action = 'metadata';
            if (!$isTestOnly) {
                $qbankService->synchronizeMetadata($file['uid']);
                if ($file['tx_qbank_remote_replaced_by'] > 0) {
                    $action = 'metadata and file';
                    $qbankService->replaceLocalMedia($file['uid']);
                }
            }
        }

        $message = '%s %s on sys_file with uid "%s".';

        return sprintf($message, $prefix, $action, $file['uid']);
    }
}
