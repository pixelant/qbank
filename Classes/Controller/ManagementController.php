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

use Pixelant\Qbank\Exception\MediaPermanentlyDeletedException;
use Pixelant\Qbank\Repository\MappingRepository;
use Pixelant\Qbank\Repository\QbankFileRepository;
use Pixelant\Qbank\Service\QbankService;
use Pixelant\Qbank\Utility\PropertyUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Module\ModuleData;
use TYPO3\CMS\Backend\Routing\UriBuilder as BackendUriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ManagementController extends ActionController
{
    protected ?ModuleData $moduleData = null;

    protected ModuleTemplate $moduleTemplate;

    /**
     * Module name for the shortcut.
     *
     * @var string
     */
    private $shortcutName;

    /**
     * ManagementController constructor.
     */
    // phpcs:disable
    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly IconFactory $iconFactory,
        protected readonly PageRenderer $pageRenderer,
        protected readonly QbankService $qbankService,
        protected readonly BackendUriBuilder $backendUriBuilder
    ) {}
    // phpcs:enable

    /**
     * Init module state.
     * This isn't done within __construct() since the controller
     * object is only created once in extbase when multiple actions are called in
     * one call. When those change module state, the second action would see old state.
     */
    public function initializeAction(): void
    {
        $this->moduleData = $this->request->getAttribute('moduleData');
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->moduleTemplate->setTitle(
            LocalizationUtility::translate(
                'LLL:EXT:qbank/Resources/Private/Language/locallang.xlf:be.module.title',
                'qbank'
            )
        );
        $this->moduleTemplate->setFlashMessageQueue($this->getFlashMessageQueue());
    }

    /**
     * Assign default variables to ModuleTemplate view
     */
    protected function initializeView(): void
    {
        $this->moduleTemplate->assignMultiple([
            'dateFormat' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'],
            'timeFormat' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'],
            'dateTimeFormat' =>
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] . ' ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'],
        ]);
        // Load JavaScript modules
        $javaScriptRenderer = $this->pageRenderer->getJavaScriptRenderer();
        $javaScriptRenderer->addJavaScriptModuleInstruction(
            JavaScriptModuleInstruction::create('@typo3/filelist/file-list.js')->instance()
        );
        $this->pageRenderer->loadJavaScriptModule('@typo3/backend/context-menu.js');
        $this->pageRenderer->loadJavaScriptModule('@typo3/backend/modal.js');
    }

    /**
     * Generates the dropdown menu.
     */
    private function generateDropdownMenu(string $currentAction): void
    {
        $this->uriBuilder->setRequest($this->request);
        $menu = $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('WebFuncJumpMenu');
        $menu->addMenuItem(
            $menu->makeMenuItem()
                ->setTitle(
                    LocalizationUtility::translate(
                        'LLL:EXT:qbank/Resources/Private/Language/locallang.xlf:be.menu_item.overview',
                        'qbank'
                    )
                )
                ->setHref($this->uriBuilder->uriFor('overview'))
                ->setActive($currentAction === 'overview')
        );
        $menu->addMenuItem(
            $menu->makeMenuItem()
                ->setTitle(
                    LocalizationUtility::translate(
                        'LLL:EXT:qbank/Resources/Private/Language/locallang.xlf:be.menu_item.list',
                        'qbank'
                    )
                )
                ->setHref($this->uriBuilder->uriFor('list'))
                ->setActive($currentAction === 'list')
        );
        $menu->addMenuItem(
            $menu->makeMenuItem()
                ->setTitle(
                    LocalizationUtility::translate(
                        'LLL:EXT:qbank/Resources/Private/Language/locallang.xlf:be.menu_item.mappings',
                        'qbank'
                    )
                )
                ->setHref($this->uriBuilder->uriFor('mappings'))
                ->setActive($currentAction === 'mappings')
        );
        $this->moduleTemplate->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
    }

    /**
     * Gets all buttons for the docHeader.
     * @param string $currentAction
     */
    private function generateButtons($currentAction): void
    {
        $this->uriBuilder->setRequest($this->request);
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
        if ($currentAction === 'mappings') {
            $addUserButton = $buttonBar->makeLinkButton()
                ->setIcon($this->iconFactory->getIcon('actions-plus', Icon::SIZE_SMALL))
                ->setTitle(
                    LocalizationUtility::translate(
                        'LLL:EXT:qbank/Resources/Private/Language/locallang.xlf:be.button.add_mapping',
                        'qbank'
                    )
                )
                ->setShowLabelText(true)
                ->setHref((string)$this->backendUriBuilder->buildUriFromRoute('record_edit', [
                    'edit' => ['tx_qbank_domain_model_mapping' => [0 => 'new']],
                    'returnUrl' => $this->request->getAttribute('normalizedParams')->getRequestUri(),
                ]));
            $buttonBar->addButton($addUserButton);
        }

        $reloadButton = $buttonBar->makeLinkButton()
            ->setHref($this->request->getAttribute('normalizedParams')->getRequestUri())
            ->setTitle(
                $this->getLanguageService()
                    ->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.reload')
            )
            ->setIcon($this->iconFactory->getIcon('actions-refresh', Icon::SIZE_SMALL));

        $buttonBar->addButton($reloadButton, ButtonBar::BUTTON_POSITION_RIGHT);
    }

    /**
     * Overview.
     */
    protected function overviewAction(): ResponseInterface
    {
        $this->generateDropdownMenu('overview');
        $this->generateButtons('overview');
        $properties = $this->qbankService->fetchMediaProperties();
        $this->moduleTemplate->assign('properties', $properties);
        return $this->moduleTemplate->renderResponse('Management/Overview');
    }

    /**
     * Mapping.
     */
    protected function mappingsAction(): ResponseInterface
    {
        $this->generateDropdownMenu('mappings');
        $this->generateButtons('mappings');
        $mappingRepository = GeneralUtility::makeInstance(MappingRepository::class);
        $mappings = $mappingRepository->findAll();
        $this->moduleTemplate->assignMultiple([
            'mappings' => $mappings,
            'mediaProperties' => $this->qbankService->fetchMediaProperties(),
            'fileProperties' => PropertyUtility::getFileProperties(),
        ]);
        return $this->moduleTemplate->renderResponse('Management/Mappings');
    }

    /**
     * List.
     */
    protected function listAction(): ResponseInterface
    {
        $this->generateDropdownMenu('list');
        $this->generateButtons('list');
        $qbankFileRepository = GeneralUtility::makeInstance(QbankFileRepository::class);
        $qbankFiles = $qbankFileRepository->findAll();
        $this->moduleTemplate->assign('qbankFiles', $qbankFiles);
        return $this->moduleTemplate->renderResponse('Management/List');
    }

    /**
     * Update metadata for file.
     */
    public function synchronizeMetadataAction()
    {
        if ($this->request->hasArgument('files')) {
            $files = $this->request->getArgument('files');
        } elseif ($this->request->hasArgument('file')) {
            $files = [$this->request->getArgument('file')];
        } else {
            $this->moduleTemplate->addFlashMessage(
                'No files could be syncronized',
                'Syncronize',
                \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR
            );
            return $this->rediectResponse('list');
        }

        foreach ($files as $file) {
            $file = (int)$file;

            if ($file <= 0) {
                continue;
            }

            try {
                $this->qbankService->synchronizeMetadata($file);
            } catch (MediaPermanentlyDeletedException $th) {
                $this->moduleTemplate->addFlashMessage(
                    $th->getMessage(),
                    '',
                    AbstractMessage::ERROR
                );
            }
        }

        $this->moduleTemplate->addFlashMessage(
            LocalizationUtility::translate(
                'LLL:EXT:qbank/Resources/Private/Language/locallang.xlf:be.action.updated-metadata',
                'qbank'
            ),
            '',
            \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::OK
        );

        return $this->rediectResponse('list');
    }

    /**
     * Replace image for file.
     */
    public function replaceLocalMediaAction()
    {
        if ($this->request->hasArgument('files')) {
            $files = $this->request->getArgument('files');
        } elseif ($this->request->hasArgument('file')) {
            $files = [$this->request->getArgument('file')];
        } else {
            $this->moduleTemplate->addFlashMessage(
                'No files could be syncronized',
                'Syncronize',
                \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR
            );
            return $this->rediectResponse('list');
        }

        foreach ($files as $file) {
            $file = (int)$file;

            if ($file <= 0) {
                continue;
            }

            $this->qbankService->replaceLocalMedia($file);

            $this->moduleTemplate->addFlashMessage(
                LocalizationUtility::translate(
                    'LLL:EXT:qbank/Resources/Private/Language/locallang.xlf:be.action.updated-file',
                    'qbank'
                ),
                '',
                \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::OK
            );
        }

        return $this->rediectResponse('list');
    }

    /**
     * Redirect to $action to avoid parameters in URL.
     *
     * @param string $action
     */
    private function rediectResponse(string $action): ResponseInterface
    {
        return new RedirectResponse($this->backendUriBuilder->buildUriFromRoute('file_qbank', ['action' => $action]));
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
