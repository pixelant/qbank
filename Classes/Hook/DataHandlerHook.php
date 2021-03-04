<?php

namespace Pixelant\Qbank\Hook;

use QBNK\QBank\API\Credentials;
use QBNK\QBank\API\QBankApi;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataHandlerHook
{
    public function processCmdmap_deleteAction($table, $id, $recordToDelete, $recordWasDeleted, $dataHandler): void
    {
        $qbankUsageId = 0;
        if ($table === 'sys_file_reference' && isset($recordToDelete['qbank_reported'])) {
            try {
                $qbankUsageId = (int)$recordToDelete['qbank_reported'];
                if ($qbankUsageId > 0) {
                    require_once __DIR__ . '/../../Contrib/autoload.php';
                    $this->config = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('qbank');
                    $credentials = new Credentials(trim($this->config['clientID']), trim($this->config['username']), trim($this->config['password']));
                    $qbankApi = new QBankApi(trim($this->config['url']), $credentials);
                    $result = $qbankApi->events()->removeUsage($qbankUsageId);
                }
            } catch (\Exeception $e) {
            } catch (\Throwable $e) {
                if ($dataHandler->enableLogging) {
                    $dataHandler->log($table, $id, 3, 0, 1, 'Attempt to remove QBank usage failed (' . $qbankUsageId . '). [' . $this->BE_USER->errorMsg . ']');
                }
            }
        }
    }
}
