<?php
declare(strict_types=1);

namespace Pixelant\Qbank\Service;

use Pixelant\Qbank\Repository\ContentElementRepository;
use Pixelant\Qbank\Repository\HubspotFormRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Service Class to get button currently in use with information
 * database and Qbank API.
 */
class UsedFormsService
{
    protected $allForms;

    /**
     * @var \Pixelant\Qbank\Repository\ContentElementRepository
     */
    protected $contentElementRepository;

    /**
     * @var \Pixelant\Qbank\Repository\HubspotFormRepository
     */
    protected $hubspotFormRepository;

    /**
     * UsedFormsService constructor.
     */
    public function __construct()
    {
        $this->contentElementRepository = GeneralUtility::makeInstance(ContentElementRepository::class);
        $this->hubspotFormRepository = GeneralUtility::makeInstance(HubspotFormRepository::class);
        $this->allForms = $this->hubspotFormRepository->getAllFormsWithGuidAsKey();
    }

    /**
     * Gets forms from database and enriches them with API information
     * API is called only once (see __construct) to reduce API calls.
     *
     * @return array
     */
    public function getFormsInUseWithDetails(): array
    {
        $contentElementsWithHubspotForm = $this->contentElementRepository->getContentElementsWithHubspotForm();
        $enrichedElements = [];
        foreach ($contentElementsWithHubspotForm as $contentElement) {
            $guid = $contentElement['hubspot_guid'];
            $formDetails = $this->allForms[$guid] ?? [];
            $contentElement['form_name'] = $formDetails['name'];
            $enrichedElements[] = $contentElement;
        }

        return $enrichedElements;
    }
}
