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

use TYPO3\CMS\Backend\Routing\Router;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Base class for qbank browser.
 */
abstract class AbstractQbankBrowser
{
    /**
     * @var ModuleTemplate
     */
    protected $moduleTemplate;

    /**
     * @var PageRenderer
     */
    protected $pageRenderer = null;

    /**
     * URL of current request.
     *
     * @var string
     */
    protected $thisScript = '';

    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * Active with TYPO3 Qbank Browser: Contains the irre obecjt id, name of the form field for which this window
     * opens - thus allows us to make references back to the main window in which the form is.
     *
     * Values:
     * 0: $targetFolder The target folder identifier, eg. "1:/user_upload/"
     * 1: $maxSize Max allowed size of files, eg. "2097152"
     * 2: $allowed Allowed file extensions, eg. "gif,jpg,jpeg,tif,bmp,pcx,tga,png,pdf,ai"
     * 3: $irreObjectId The irre object id, eg. "data-35-tt_content-10-image-sys_file_reference"
     * list($targetFolder, $maxSize, $allowed, $irreObjectId) = explode('|', $this->bparams);
     *
     * @var string
     */
    protected $bparams;

    /**
     * Construct.
     */
    public function __construct()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->moduleTemplate = $objectManager->get(DocumentTemplate::class);
        $this->iconFactory = $objectManager->get(IconFactory::class);
        $this->pageRenderer = $objectManager->get(PageRenderer::class);
        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Qbank/QbankSource');
        $this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Qbank/Qbank');
        $this->pageRenderer->addCssFile(\TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/typo3conf/ext/qbank/Resources/Public/Css/qbankfile.css');

        $this->initialize();
    }

    /**
     * Main initialization.
     *
     * @return void
     */
    protected function initialize(): void
    {
        $this->determineScriptUrl();
        $this->initVariables();
    }

    /**
     * Sets the script url depending on being a module or script request.
     *
     * @return void
     */
    protected function determineScriptUrl(): void
    {
        if ($routePath = GeneralUtility::_GP('route')) {
            $router = GeneralUtility::makeInstance(Router::class);
            $route = $router->match($routePath);
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $this->thisScript = (string)$uriBuilder->buildUriFromRoute($route->getOption('_identifier'));
        } elseif ($moduleName = GeneralUtility::_GP('M')) {
            $this->thisScript = GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute($moduleName, []);
        } else {
            $this->thisScript = GeneralUtility::getIndpEnv('SCRIPT_NAME');
        }
    }

    /**
     * @return void
     */
    protected function initVariables(): void
    {
        $this->bparams = GeneralUtility::_GP('bparams');
        if ($this->bparams === null) {
            $this->bparams = '';
        }
    }

    /**
     * Initialize document template object.
     *
     *  @return void
     */
    protected function initDocumentTemplate(): void
    {
        $this->moduleTemplate->bodyTagId = 'typo3-browse-links-php';

        $bodyDataAttributes = array_merge(
            $this->getBParamDataAttributes(),
            $this->getBodyTagAttributes()
        );
        foreach ($bodyDataAttributes as $attributeName => $value) {
            $this->moduleTemplate->bodyTagAdditions .= ' ' . $attributeName . '="' . htmlspecialchars($value) . '"';
        }
        // unset the default jumpToUrl() function as we ship our own
        unset($this->moduleTemplate->JScodeArray['jumpToUrl']);
    }

    /**
     * @return string[] Array of body-tag attributes
     */
    abstract protected function getBodyTagAttributes();

    /**
     * Splits parts of $this->bparams and returns needed data attributes for the Javascript.
     *
     * @return string[] Data attributes for Javascript
     */
    protected function getBParamDataAttributes()
    {
        [$targetFolder, $maxSize, $allowed, $irreObjectId] = explode('|', $this->bparams);

        return [
            'data-this-script-url' => strpos($this->thisScript, '?') === false ? $this->thisScript . '?' : $this->thisScript . '&',
            'data-target-folder' => $targetFolder,
            'data-max-size' => $maxSize,
            'data-allowed' => $allowed,
            'data-irre-object-id' => $irreObjectId,
        ];
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
