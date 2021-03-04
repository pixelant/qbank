<?php

namespace Pixelant\Qbank\Browser;

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

use Pixelant\Qbank\Utility\QbankUtility;
use QBNK\QBank\API\Credentials;
use QBNK\QBank\API\QBankApi;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Recordlist\Browser\ElementBrowserInterface;
use TYPO3\CMS\Recordlist\Tree\View\LinkParameterProviderInterface;

/**
 * Browser for files.
 */
class QbankFileBrowser extends AbstractQbankBrowser implements ElementBrowserInterface, LinkParameterProviderInterface
{
    public $config;

    public $token;

    protected function initialize(): void
    {
        parent::initialize();
        require_once \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/typo3conf/ext/qbank/Contrib/autoload.php';
        $this->config = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('qbank');

        try {
            $credentials = new Credentials(trim($this->config['clientID']), trim($this->config['username']), trim($this->config['password']));
            $qbankApi = new QBankApi(trim($this->config['url']), $credentials);
            $qbankApi->accounts()->retrieveCurrentUser();
            $this->tokens = $qbankApi->getTokens();
            $this->token = $this->tokens['accessToken']->getToken();
            $deploymentSites = QbankUtility::getSettingDeploymentSites();
            $jsVariables = PHP_EOL . 'var token = \'' . $this->token . '\';';
            $jsVariables .= PHP_EOL . 'var clienturl = \'' . $this->config['url'] . '\';';
            $jsVariables .= PHP_EOL . 'var deploymentSites = \'' . $deploymentSites . '\';';
            $this->pageRenderer->addJsInlineCode(
                'variables',
                $jsVariables
            );
        } catch (\Exception $e) {
        } catch (\Throwable $e) {
        }
    }

    public function render()
    {
        $this->initDocumentTemplate();

        // If we have no token, API connection failed
        if (!$this->token) {
            $message = GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\Messaging\FlashMessage::class,
                'Could not connect to QBank API. Please check qbank_connector extension configuration.',
                'Error',
                \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
            );
            $flashMessageService = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Messaging\FlashMessageService::class);
            $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $messageQueue->addMessage($message);
            $content = $this->moduleTemplate->startPage('Qbank file selector');
            $content .= $this->moduleTemplate->getFlashMessages();

            return $content;
        }
        $content = $this->moduleTemplate->startPage('Qbank file selector');
        $content .= '<div id="overlay"></div>';
        $content .= '<div id="wrapper"></div>';
        $content .= $this->moduleTemplate->endPage();

        return $this->moduleTemplate->insertStylesAndJS($content);
    }

    /**
     * @return string[] Array of body-tag attributes
     */
    protected function getBodyTagAttributes()
    {
        return [
            'data-mode' => 'qbankfile',
        ];
    }

    /**
     * Session data for this class can be set from outside with this method.
     *
     * @param mixed[] $data Session data array
     * @return array[] Session data and boolean which indicates that data needs to be stored in session because it's changed
     */
    public function processSessionData($data)
    {
        if ($this->expandFolder !== null) {
            $data['expandFolder'] = $this->expandFolder;
            $store = true;
        } else {
            $this->expandFolder = $data['expandFolder'];
            $store = false;
        }

        return [$data, $store];
    }

    /**
     * Returns the URL of the current script.
     *
     * @return string
     */
    public function getScriptUrl()
    {
        return $this->thisScript;
    }

    /**
     * @param array $values Array of values to include into the parameters
     * @return string[] Array of parameters which have to be added to URLs
     */
    public function getUrlParameters(array $values)
    {
        return [
            'mode' => 'file',
            'expandFolder' => $values['identifier'] ?? $this->expandFolder,
            'bparams' => $this->bparams,
        ];
    }

    /**
     * @param array $values Values to be checked
     * @return bool Returns TRUE if the given values match the currently selected item
     */
    public function isCurrentlySelectedItem(array $values)
    {
        return false;
    }
}
