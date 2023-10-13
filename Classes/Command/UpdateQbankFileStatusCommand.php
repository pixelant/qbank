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

use Pixelant\Qbank\Repository\MediaRepository;
use Pixelant\Qbank\Repository\QbankFileRepository;
use Pixelant\Qbank\Service\QbankService;
use Pixelant\Qbank\Utility\QbankUtility;
use QBNK\QBank\API\Exception\RequestException;
use QBNK\QBank\API\Model\MediaResponse;
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
class UpdateQbankFileStatusCommand extends Command
{
    /**
     * Configure the command by defining the name, options and arguments.
     */
    public function configure(): void
    {
        $this->setDescription('Update status of downloaded QBank files.')
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Limit number of files to check per run. (default 10)',
                10
            )
            ->addOption(
                'interval',
                'i',
                InputOption::VALUE_OPTIONAL,
                'Interval (in seconds) until files are checked again (default 86400).',
                86400
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
        $interval = (int)$input->getOption('interval');

        /** @var QbankFileRepository $qbankFileRepository */
        $qbankFileRepository = GeneralUtility::makeInstance(QbankFileRepository::class);
        $updateQueue = $qbankFileRepository->fetchStatusUpdateQueue($limit, $interval);

        $io->title('Update QBank file status.');

        if (count($updateQueue) === 0) {
            $io->success('Status has already been updated for all QBank files.');

            return 0;
        }

        $io->section('Checking ' . count($updateQueue) . ' files.');

        /** @var QbankService $qbankService */
        $qbankService = GeneralUtility::makeInstance(QbankService::class);

        /** @var MediaRepository $mediaRepository */
        $mediaRepository = GeneralUtility::makeInstance(MediaRepository::class);

        foreach ($updateQueue as $file) {
            try {
                /** @var MediaResponse $media */
                $media = $mediaRepository->findById($file['tx_qbank_id']);
            } catch (RequestException $re) {
                $io->writeln('QBank file was not found: ' . $file['tx_qbank_id']);

                if (QbankUtility::qbankRequestExceptionStatesMediaIsDeleted($re)) {
                    $io->writeln('Update sys_file, set tx_qbank_remote_is_deleted to 1: ' . $file['uid']);
                    $qbankService->updateFileRemoteIsDeleted($file['uid']);

                    continue;
                }
            }

            $remoteUpdate = (int)$media->getUpdated()->getTimestamp();
            $remoteReplacedBy = (int)$media->getReplacedBy();
            $message = '%s sys_file, set remote change to "%s", replaced: "%s" on sys_file with uid "%s".';
            if ($isTestOnly) {
                $prefix = 'TESTONLY: Would update';
            } else {
                $prefix = 'Update';
                $qbankService->updateFileRemoteChange($file['uid'], $remoteUpdate, $remoteReplacedBy);
            }
            $io->writeln(sprintf($message, $prefix, $remoteUpdate, $remoteReplacedBy, $file['uid']));
        }

        $io->success('Status has been updated for ' . count($updateQueue) . ' QBank files.');

        return 0;
    }
}
