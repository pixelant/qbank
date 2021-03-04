<?php

namespace Pixelant\Qbank\Controller;

/*
 *
 * This file is part of the "Qbank" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2021
 *
 */
/**
 * QbankMediaController.
 */
class QbankMediaController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * qbankMediaRepository.
     *
     * @var \Pixelant\Qbank\Domain\Repository\QbankMediaRepository
     */
    protected $qbankMediaRepository = null;

    /**
     * action list.
     *
     * @return void
     */
    public function listAction(): void
    {
        // $medias = $this->mediaRepository->findAll();
        $this->view->assign('medias', $medias);
    }

    /**
     * action show.
     *
     * @return void
     */
    public function showAction(): void
    {
        $this->view->assign('media', $media);
    }
}
