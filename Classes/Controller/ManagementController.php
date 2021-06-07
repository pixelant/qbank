<?php

declare(strict_types=1);

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

namespace Pixelant\Qbank\Controller;

use Pixelant\Qbank\Repository\MappingRepository;
use Pixelant\Qbank\Repository\QbankFileRepository;
use Pixelant\Qbank\Service\QbankService;
use Pixelant\Qbank\Utility\PropertyUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3Fluid\Fluid\View\ViewInterface;

/**
 * QBank Management Controller.
 *
 * Scope: backend
 * @internal
 */
final class ManagementController
{
    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var array
     */
    private $arguments = [];

    /**
     * ModuleTemplate object.
     *
     * @var ModuleTemplate
     */
    private $moduleTemplate;

    /**
     * @var ViewInterface
     */
    private $view;

    /**
     * Module name for the shortcut.
     *
     * @var string
     */
    private $shortcutName;

    /**
     * @var QbankService
     */
    private $qbankService;

    /**
     * @var IconFactory
     */
    private $iconFactory;

    /**
     * Actions to create menu for.
     */
    private $actions = ['overview', 'mappings', 'list'];

    /**
     * ManagementController constructor.
     */
    public function __construct()
    {
        $this->qbankService = GeneralUtility::makeInstance(QbankService::class);
        $this->moduleTemplate = GeneralUtility::makeInstance(ModuleTemplate::class);
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
    }

    /**
     * Injects the request object for the current request, and renders correct action.
     *
     * @param ServerRequestInterface $request the current request
     * @return ResponseInterface the response with the content
     */
    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        $this->arguments = array_merge_recursive(
            $request->getQueryParams(),
            $request->getParsedBody() ?? []
        );

        $action = $this->arguments['action'] ?? 'overview';

        $this->generateDropdownMenu($request, $action);
        $this->generateButtons($action);

        $this->initializeView($action);

        $actionFunction = $action . 'Action';
        if (method_exists($this, $actionFunction)) {
            $this->{$actionFunction}();
        } else {
            $this->overviewAction();
        }

        $this->moduleTemplate->setContent($this->view->render());

        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * @param string $templateName
     */
    private function initializeView(string $templateName): void
    {
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);
        $this->view->setTemplate($templateName);
        $this->view->setTemplateRootPaths(['EXT:qbank/Resources/Private/Templates/Management']);
        $this->view->setPartialRootPaths(['EXT:qbank/Resources/Private/Partials']);
        $this->view->setLayoutRootPaths(['EXT:qbank/Resources/Private/Layouts']);
        $this->view->getRequest()->setControllerExtensionName('Qbank');
        $this->view->assign(
            'settings',
            [
                'dateFormat' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'],
            ],
        );
        // Info window is included in this.
        $this->moduleTemplate->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Filelist/FileList');
    }

    /**
     * Generates the dropdown menu.
     *
     * @param ServerRequestInterface $request
     * @param string $action
     * @return void
     */
    private function generateDropdownMenu(ServerRequestInterface $request, string $action): void
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $lang = $this->getLanguageService();
        $lang->includeLLFile('EXT:qbank/Resources/Private/Language/locallang.xlf');
        $menu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('WebFuncJumpMenu');

        foreach ($this->actions as $menuAction) {
            $menuItem = $menu
                ->makeMenuItem()
                ->setActive($action === $menuAction)
                ->setHref(
                    $uriBuilder->buildUriFromRoute('file_qbank', ['action' => $menuAction])
                )
                ->setTitle($lang->getLL('be.menu_item.' . $menuAction));
            $menu->addMenuItem($menuItem);
        }

        $this->shortcutName = $lang->getLL('be.menu_item.qbank_overview');
        $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

    /**
     * Gets all buttons for the docHeader.
     * @param mixed $action
     */
    private function generateButtons($action): void
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();

        if ($action === 'mappings') {
            $newRecordButton = $buttonBar->makeLinkButton()
                ->setHref((string)$uriBuilder->buildUriFromRoute(
                    'record_edit',
                    [
                        'edit' => [
                            'tx_qbank_domain_model_mapping' => ['new'],
                        ],
                        'returnUrl' => (string)$uriBuilder->buildUriFromRoute('file_qbank', ['action' => 'mappings']),
                    ]
                ))
                ->setTitle($this->getLanguageService()->getLL('be.button.add_mapping'))
                ->setIcon($this->iconFactory->getIcon('actions-add', Icon::SIZE_SMALL));
            $buttonBar->addButton($newRecordButton, ButtonBar::BUTTON_POSITION_LEFT, 1);
        }

        $shortcutButton = $buttonBar->makeShortcutButton()
            ->setModuleName('file_qbank')
            ->setGetVariables(['action', 'extension'])
            ->setDisplayName($this->shortcutName);
        $buttonBar->addButton($shortcutButton);
    }

    /**
     * Overview.
     */
    private function overviewAction(): void
    {
        $properties = $this->qbankService->fetchMediaProperties();
        // $bySystemName = $this->qbankService->fetchPropertyTypeBySystemName('akeywordsystemname');
        $this->view->assign('properties', $properties);
    }

    /**
     * Mapping.
     */
    private function mappingsAction(): void
    {
        $mappingRepository = GeneralUtility::makeInstance(MappingRepository::class);
        $mappings = $mappingRepository->findAll();
        $this->view->assign('mappings', $mappings);
        $this->view->assign('mediaProperties', $this->qbankService->fetchMediaProperties());
        $this->view->assign('fileProperties', PropertyUtility::getFileProperties());
    }

    /**
     * List.
     */
    private function listAction(): void
    {
        $qbankFileRepository = GeneralUtility::makeInstance(QbankFileRepository::class);
        $qbankFiles = $qbankFileRepository->findAll();
        $this->view->assign('qbankFiles', $qbankFiles);
    }

    /**
     * Update metadata for file.
     */
    public function synchronizeMetadataAction(): void
    {
        $files = $this->arguments['files'] ?? [$this->arguments['file']];

        foreach ($files as $file) {
            $file = (int)$file;

            if ($file <= 0) {
                continue;
            }

            $this->qbankService->synchronizeMetadata($file);
        }

        $this->forward('list');
    }

    /**
     * Forward execution to $action.
     *
     * @param string $action
     */
    private function forward(string $action): void
    {
        $this->initializeView($action);

        $methodName = $action . 'Action';

        $this->{$methodName}();
    }

    /**
     * @return BackendUserAuthentication
     */
    private function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return LanguageService
     */
    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
