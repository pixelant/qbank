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

namespace Pixelant\Qbank\ElementBrowser;

use TYPO3\CMS\Backend\ElementBrowser\AbstractElementBrowser;
use TYPO3\CMS\Backend\ElementBrowser\ElementBrowserInterface;

/**
 * Plan is to show a QBank file selector.
 * Not sure we need this, or it can be done in another way.
 * Not sure how it worked in TYPO3 v11 version.
 * This class is based on vendor/typo3/cms-backend/Classes/ElementBrowser/DatabaseBrowser.php.
 *
 */
class QbankBrowser extends AbstractElementBrowser implements ElementBrowserInterface
{
    protected string $identifier = 'qbank';

    /**
     * When you click a page title/expand icon to see the content of a certain page, this
     * value will contain the ID of the expanded page.
     * If the value is NOT set by GET parameter, then it will be restored from the module session data.
     *
     * @var int|null
     */
    protected $expandPage;
    protected array $modTSconfig = [];

    protected function initialize()
    {
        parent::initialize();
        /*$this->pageRenderer->loadJavaScriptModule('@typo3/backend/browse-database.js');
        $this->pageRenderer->loadJavaScriptModule('@typo3/backend/tree/page-browser.js');
        $this->pageRenderer->loadJavaScriptModule('@typo3/backend/column-selector-button.js');
        $this->pageRenderer->loadJavaScriptModule('@typo3/backend/recordlist.js');
        $this->pageRenderer->loadJavaScriptModule('@typo3/backend/record-search.js');*/
    }

    protected function initVariables()
    {
        parent::initVariables();
        // $this->expandPage = $this->getRequest()->getParsedBody()['expandPage'] ?? $this->getRequest()->getQueryParams()['expandPage'] ?? null;
    }

    /**
     * Session data for this class can be set from outside with this method.
     *
     * @param mixed[] $data Session data array
     * @return array<int, array|bool> Session data and boolean which indicates that data needs to be stored in session because it's changed
     */
    public function processSessionData($data)
    {
        if ($this->expandPage !== null) {
            $data['expandPage'] = $this->expandPage;
            $store = true;
        } else {
            $this->expandPage = (int)($data['expandPage'] ?? 0);
            $store = false;
        }
        return [$data, $store];
    }

    /**
     * @return string HTML content
     */
    public function render()
    {
        // $this->pageRenderer->setTitle($this->getLanguageService()->sL('LLL:EXT:backend/Resources/Private/Language/locallang_browse_links.xlf:recordSelector'));
        $this->pageRenderer->setTitle('QBank file selector');
        $view = $this->view;
        $content = '<h1>QBank</h1>';
        $content .= '<p>Might be used for browsing QBank files?</p>';
        $content .= '<p>Not really sure how it worked before.</p>';
        $content .= 'This script: <code>vendor/pixelant/qbank/Classes/ElementBrowser/QbankBrowser.php</code>';
        $this->pageRenderer->setBodyContent('<body ' . $this->getBodyTagParameters() . '>' . $content);
        return $this->pageRenderer->render();
    }

    /**
     * @param array $values Array of values to include into the parameters
     * @return string[] Array of parameters which have to be added to URLs
     */
    public function getUrlParameters(array $values)
    {
        $pid = $values['pid'] ?? $this->expandPage;
        return [
            'mode' => 'db',
            'expandPage' => $pid,
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

    /**
     * Returns the URL of the current script
     *
     * @return string
     */
    public function getScriptUrl()
    {
        return $this->thisScript;
    }
}
