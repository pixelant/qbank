<?php

namespace Pixelant\Qbank\Controller;

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
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\Index\FileIndexRepository;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class OnlineMediaController handles uploading online media.
 */
class QbankFalMediaController
{
    /**
     * Target folder identifier.
     *
     * @var string
     */
    protected $targetFolderIdentifier = null;

    /**
     * Array of allowed file extensions.
     *
     * @var array
     */
    protected $allowedExtensions = [];

    /**
     * Max file size in bytes.
     *
     * @var int
     */
    protected $maxSize = 0;

    /**
     * Media array returned from qbank-connector js.
     *
     * @var array
     */
    protected $media = [];

    /**
     * Image array returned from qbank-connector js.
     *
     * @var array
     */
    protected $image = [];

    /**
     * Previous usage array returned from qbank-connector js.
     *
     * @var array
     */
    protected $previousUsage = [];

    /**
     * Array to store error messages in.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * The id of the file that where downloaded/created.
     *
     * @var int
     */
    protected $file = null;

    /**
     * The id of the file that where downloaded/created.
     *
     * @var ResourceFactory
     */
    protected $resourceFactory = null;

    /**
     * Initialize class.
     *
     * @param ServerRequestInterface $request Request
     *
     * @return void
     */
    protected function initialize($request): void
    {
        $this->targetFolderIdentifier = $request->getParsedBody()['targetFolder'];
        $this->allowedExtensions = GeneralUtility::trimExplode(',', $request->getParsedBody()['allowed'] ?: '');
        $this->maxSize = (int)$request->getParsedBody()['maxSize'];
        $this->media = $request->getParsedBody()['media'];
        $this->image = $request->getParsedBody()['image'];
        $this->previousUsage = $request->getParsedBody()['previousUsage'];
        $this->resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
    }

    /**
     * AJAX endpoint for storing the URL as a sys_file record.
     *
     * @param ServerRequestInterface $request
     * @return JsonResponse
     */
    public function createAction(ServerRequestInterface $request): JsonResponse
    {
        $data = [];

        try {
            $this->initialize($request);

            $this->createFile();
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        if (isset($this->file)) {
            $data['file'] = $this->file->getUid();
        } else {
            if (count($this->errors) > 0) {
                $errorText = $this->getErrorsAsString();
            } else {
                $errorText = 'Unknown error';
            }
            $data['error'] = $errorText;
        }

        // $response->getBody()->write(json_encode($data));
        return new JsonResponse($data);
    }

    /**
     * Check variables, configuration etc. and if everything is ok create file.
     *
     * @return void
     */
    protected function createFile(): void
    {
        $this->checkPreviousUsage();

        $this->checkMediaUrl();

        $this->checkAllowedExtensions();

        $this->checkMimetypeVideo();

        $this->checkImage();

        $this->setMetaData();
    }

    /**
     * Check if qbank connector returned a prevoius usage.
     *
     * @return void
     */
    protected function checkPreviousUsage(): void
    {
        // If template and context localID, used same as reported usage for
        if (isset($this->previousUsage['context']['localID'])) {
            $previusFileId = (int)$this->previousUsage['context']['localID'];
            if ($previusFileId > 0) {
                try {
                    $existingFile = $this->resourceFactory->getFileObject($previusFileId);
                    $this->file = $existingFile;
                } catch (\TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException $e) {
                    $this->errors[] = QbankUtility::getLabel('Error.CouldNotFindFileToResuse');
                }
            }
        }
    }

    /**
     * Validate the media url from qbank connector.
     *
     * @return void
     */
    protected function checkMediaUrl(): void
    {
        // Check if given url is valid
        $url = $this->image[0]['url'];
        if (!GeneralUtility::isValidUrl($url)) {
            $this->errors[] = QbankUtility::getLabel('Error.InvalidUrl');
        }
    }

    /**
     * Validate the file extension of the media from qbank connector.
     *
     * @return void
     */
    protected function checkAllowedExtensions(): void
    {
        // Check if the extension is allowed
        $extension = $this->media['extension'];
        if (!in_array($extension, $this->allowedExtensions, true)) {
            $this->errors[] = QbankUtility::getLabel(
                'Error.FileExtensionNotAllowed',
                [
                    $extension,
                    implode(',', $this->allowedExtensions),
                ]
            );
        }
    }

    /**
     * Check for media with mimetype video.
     *
     * @return void
     */
    protected function checkMimetypeVideo()
    {
        // File is already set or errors exists
        if (isset($this->file) || count($this->errors) > 0) {
            return;
        }

        if ($this->media['mimetype']['classification'] === 'video') {
            // Invalid "extension" (qbank file is not allowed)
            if (!in_array('qbank', $this->allowedExtensions, true)) {
                $this->errors[] = QbankUtility::getLabel('Error.QbankOnlineMediaNotEnabledForField');

                return $response;
            }

            // Check deployed files
            $deployedMedia = $this->media['deployedFiles'];
            if (is_array($deployedMedia) && count($deployedMedia) > 0) {
                $configuration = QbankUtility::getConfiguration();
                $publishPlaceId = $this->getPublishPlaceId($deployedMedia);

                // Validate ts setup
                $validateConfigurationResult = $this->validatePublishPlaceConfiguration($publishPlaceId, $configuration);
                if ($validateConfigurationResult['isValid'] === false) {
                    if (is_array($validateConfigurationResult['messages'])) {
                        foreach ($validateConfigurationResult['messages'] as $validationMessage) {
                            $this->errors[] = $validationMessage;
                        }
                    } else {
                        $this->errors[] = QbankUtility::getLabel('Error.ValidationFailedWithoutMessages');
                    }

                    return;
                }

                // Fetch files by configuration
                $mediaId = null;
                $poster = $this->getPosterUrl($publishPlaceId, $deployedMedia, $configuration);
                $mp4 = $this->getMp4VideoUrl($publishPlaceId, $deployedMedia, $configuration);
                $ogg = $this->getOggVideoUrl($publishPlaceId, $deployedMedia, $configuration);
                $webm = $this->getWebmVideoUrl($publishPlaceId, $deployedMedia, $configuration);
                $title = $this->media['name'];

                // Build mediaId
                if ($publishPlaceId && ($mp4 || $ogg || $webm)) {
                    $mediaId = QbankUtility::encodeMediaId(
                        $publishPlaceId,
                        $poster,
                        $mp4,
                        $ogg,
                        $webm,
                        $title,
                        $author
                    );
                }

                // Check if media id was built
                if ($mediaId) {
                    $file = $this->addQbankMediaFromUrl($mediaId, $this->targetFolderIdentifier);
                    if ($file !== null) {
                        $this->file = $file;
                    } else {
                        $this->errors[] = QbankUtility::getLabel('Error.InvalidUrl');
                    }
                } else {
                    $this->errors[] = QbankUtility::getLabel('Error.DeployedVideoNotFound');
                }
            } else {
                $this->errors[] = QbankUtility::getLabel('Error.NoDeployedVideo');
            }
        }
    }

    /**
     * Check qbank connector for image.
     *
     * @return void
     */
    protected function checkImage(): void
    {
        // File is already set or errors exists
        if (isset($this->file) || count($this->errors) > 0) {
            return;
        }

        // Create a temporary file and try to download the file from url
        $url = $this->image[0]['url'];
        $tempFile = GeneralUtility::tempnam('qbank_temp_file_');
        if (!empty($url) && !empty($tempFile)) {
            $downloadResult = QbankUtility::getFile($url, $tempFile);
            if ($downloadResult['success'] !== true) {
                // Revmove tempFile and set response
                GeneralUtility::unlink_tempfile($tempFile);
                if ($downloadResult['errorCode']) {
                    $this->errors[] = $downloadResult['errorMessage'] . ' (' . $downloadResult['errorCode'] . ')';
                } else {
                    $this->errors[] = QbankUtility::getLabel('Error.DownloadFailed', [$downloadResult['status']]);
                }

                return;
            }
        }

        // QBank only returns size of the original image, so we need to check size after we downloaded it.
        $tempFileSize = filesize($tempFile);
        if ($tempFileSize > $this->maxSize) {
            // Revmove tempFile and set response
            GeneralUtility::unlink_tempfile($tempFile);
            $this->errors[] = QbankUtility::getLabel(
                'Error.FileSizeExceeded',
                [
                    GeneralUtility::formatSize($this->maxSize),
                    GeneralUtility::formatSize($tempFileSize),
                ]
            );

            return;
        }

        // Create a new file or return an existing file and remove tempFile
        $fileName = QbankUtility::generateFileName(
            $this->media['filename'],
            $this->media['mediaId'],
            $this->media['extension'],
            $this->image[0]['template']
        );
        $file = $this->addFileFromTemporaryFile($tempFile, $this->targetFolderIdentifier, $fileName);
        GeneralUtility::unlink_tempfile($tempFile);
        if ($file !== null) {
            $this->file = $file;
        } else {
            $this->errors[] = QbankUtility::getLabel('Error.FailedToCreateFile');
        }
    }

    protected function getErrorsAsString()
    {
        $errorText = '';
        if (count($this->errors) > 0) {
            foreach ($this->errors as $error) {
                if (strlen($errorText) > 0) {
                    $errorText .= ', ';
                }
                $errorText .= $error;
            }
        }

        return $errorText;
    }

    /**
     * Set metadata for file.
     *
     * @return void
     */
    protected function setMetaData(): void
    {
        // Check that a file is set
        if (!isset($this->file)) {
            return;
        }

        // Update metadata if mappings configuration exists
        $metaData = [];
        // Set some default metaData, can be overridden by page ts
        $metaData['title'] = $this->media['name'];
        $metaData['download_name'] = $this->media['filename'];
        $metaData['qbank_id'] = $this->media['mediaId'];
        $metaData['qbank_params'] = $this->image[0]['template'];
        if (isset($this->image[0]['crop'])) {
            $metaData['qbank_crop'] = json_encode($this->image[0]['crop']);
        }
        // get mappings configured in ts
        $mappings = QbankUtility::getSettingMappings();
        if ($mappings) {
            $metaData = QbankUtility::generateMetaData($metaData, $mappings, $this->media);
        }
        $this->updateMetaData($this->file, $metaData);
    }

    /**
     * @param string $url
     * @param string $targetFolderIdentifier
     * @return File|null
     */
    protected function addQbankMediaFromUrl($url, $targetFolderIdentifier)
    {
        $targetFolder = null;
        if ($targetFolderIdentifier) {
            try {
                $targetFolder = ResourceFactory::getInstance()->getFolderObjectFromCombinedIdentifier($targetFolderIdentifier);
            } catch (\Exception $e) {
                $targetFolder = null;
            }
        }
        if ($targetFolder === null) {
            $targetFolder = $this->getBackendUser()->getDefaultUploadFolder();
        }

        return OnlineMediaHelperRegistry::getInstance()->transformUrlToFile($url, $targetFolder, ['qbank']);
    }

    /**
     * Adds file to TYPO3 from a temporary file or fetch existing.
     *
     * @param string $tempFileName
     * @param string $targetFolderIdentifier
     * @param string $fileName
     *
     * @return File|null
     */
    protected function addFileFromTemporaryFile($tempFileName, $targetFolderIdentifier, $fileName)
    {
        $targetFolder = null;
        if ($targetFolderIdentifier) {
            try {
                $targetFolder = $this->resourceFactory->getFolderObjectFromCombinedIdentifier($targetFolderIdentifier);
            } catch (\Exception $e) {
                $targetFolder = null;
            }
        }
        if ($targetFolder === null) {
            $targetFolder = $this->getBackendUser()->getDefaultUploadFolder();
        }

        try {
            $fileContentHash = sha1(GeneralUtility::getUrl($tempFileName));
            $newFile = $this->findExistingFileByContentHashAndFilename($fileContentHash, $targetFolder, $fileName);
            if ($newFile === null) {
                $newFile = $targetFolder->addFile($tempFileName, $fileName, \TYPO3\CMS\Core\Resource\DuplicationBehavior::RENAME);
            }
        } catch (\Exception $e) {
            $newFile = null;
        }

        return $newFile;
    }

    /**
     * Update file metaData.
     *
     * @return void
     * @param mixed $file
     * @param mixed $metaData
     */
    protected function updateMetaData($file, $metaData): void
    {
        if (is_array($metaData) && count($metaData) > 0) {
            $this->getMetaDataRepository()->update($file->getUid(), $metaData);
        }
    }

    /**
     * Search for files with same name by content hash in indexed storage.
     *
     * @param string $fileHash
     * @param Folder $targetFolder
     * @param string $fileName
     * @return File|null
     */
    protected function findExistingFileByContentHashAndFilename($fileHash, Folder $targetFolder, $fileName)
    {
        $file = null;
        $files = $this->getFileIndexRepository()->findByContentHash($fileHash);

        if (!empty($files)) {
            foreach ($files as $fileIndexEntry) {
                if ($fileIndexEntry['folder_hash'] === $targetFolder->getHashedIdentifier()
                    && (int) $fileIndexEntry['storage'] === $targetFolder->getStorage()->getUid()
                    && $fileIndexEntry['name'] === $fileName) {
                    $file = $this->resourceFactory->getFileObject($fileIndexEntry['uid'], $fileIndexEntry);

                    break;
                }
            }
        }

        return $file;
    }

    /**
     * Fetch publishPlaceId (deploymentSiteId) from deployed media.
     *
     * @param array $deployedMedia Deployed media
     *
     * @return int|null
     */
    protected function getPublishPlaceId($deployedMedia)
    {
        $publishPlaceId = null;
        // Does a publishPlaces page ts mapping exist for this deployedMedia?
        foreach ($deployedMedia as $key => $deployedMediaValues) {
            // Pick first deploymentSiteId
            if ($publishPlaceId === null && (int)$deployedMediaValues['deploymentSiteId'] > 0) {
                $publishPlaceId = $deployedMediaValues['deploymentSiteId'];
            }
        }

        return $publishPlaceId;
    }

    /**
     * Validate Configuration by publish place.
     *
     * @param int   $publishPlaceId Publish place id
     * @param array $configuration  Configuration
     *
     * @return array
     */
    protected function validatePublishPlaceConfiguration($publishPlaceId, $configuration)
    {
        $result = [
            'isValid' => true,
            'messages' => [],
        ];
        if (!isset($configuration['mappings']['publishPlaces'][$publishPlaceId])) {
            $result['isValid'] = false;
            $result['messages'][] = QbankUtility::getLabel('Error.ConfigurationMissingForPublishPlace', [$publishPlaceId]);
        }
        if ($this->getPosterImageTemplateId($publishPlaceId, $configuration) === null) {
            $result['isValid'] = false;
            $result['messages'][] = QbankUtility::getLabel('Error.ConfigurationMissingPosterTemplateIdPublishPlace', [$publishPlaceId]);
        }
        $mp4VideoTemplateId = $this->getMp4VideoTemplateId($publishPlaceId, $configuration);
        $oggVideoTemplateId = $this->getOggVideoTemplateId($publishPlaceId, $configuration);
        $webmVideoTemplateId = $this->getWebmVideoTemplateId($publishPlaceId, $configuration);
        if (!isset($mp4VideoTemplateId)
            || !isset($mp4VideoTemplateId)
            || !isset($mp4VideoTemplateId)
        ) {
            $result['isValid'] = false;
            $result['messages'][] = QbankUtility::getLabel('Error.ConfigurationMissingVideoTemplateIdPublishPlace', [$publishPlaceId]);
        }

        return $result;
    }

    /**
     * Get "Poster" ImageTemplateId.
     *
     * @param int   $publishPlaceId Publish place id
     * @param array $configuration  Configuration
     *
     * @return int|null
     */
    protected function getPosterImageTemplateId($publishPlaceId, $configuration)
    {
        if (isset($configuration['mappings']['publishPlaces'][$publishPlaceId]['posterImageTemplateId'])) {
            if ((int)$configuration['mappings']['publishPlaces'][$publishPlaceId]['posterImageTemplateId'] > 0) {
                return (int)$configuration['mappings']['publishPlaces'][$publishPlaceId]['posterImageTemplateId'];
            }
        }

        return null;
    }

    /**
     * Get "mp4" VideoTemplateId.
     *
     * @param int   $publishPlaceId Publish place id
     * @param array $configuration  Configuration
     *
     * @return int|null
     */
    protected function getMp4VideoTemplateId($publishPlaceId, $configuration)
    {
        if (isset($configuration['mappings']['publishPlaces'][$publishPlaceId]['mp4VideoTemplateId'])) {
            if ((int)$configuration['mappings']['publishPlaces'][$publishPlaceId]['mp4VideoTemplateId'] > 0) {
                return (int)$configuration['mappings']['publishPlaces'][$publishPlaceId]['mp4VideoTemplateId'];
            }
        }

        return null;
    }

    /**
     * Get "ogg" VideoTemplateId.
     *
     * @param int   $publishPlaceId Publish place id
     * @param array $configuration  Configuration
     *
     * @return int|null
     */
    protected function getOggVideoTemplateId($publishPlaceId, $configuration)
    {
        if (isset($configuration['mappings']['publishPlaces'][$publishPlaceId]['oggVideoTemplateId'])) {
            if ((int)$configuration['mappings']['publishPlaces'][$publishPlaceId]['oggVideoTemplateId'] > 0) {
                return (int)$configuration['mappings']['publishPlaces'][$publishPlaceId]['oggVideoTemplateId'];
            }
        }

        return null;
    }

    /**
     * Get "webm" VideoTemplateId.
     *
     * @param int   $publishPlaceId Publish place id
     * @param array $configuration  Configuration
     *
     * @return int|null
     */
    protected function getWebmVideoTemplateId($publishPlaceId, $configuration)
    {
        if (isset($configuration['mappings']['publishPlaces'][$publishPlaceId]['webmVideoTemplateId'])) {
            if ((int)$configuration['mappings']['publishPlaces'][$publishPlaceId]['webmVideoTemplateId'] > 0) {
                return (int)$configuration['mappings']['publishPlaces'][$publishPlaceId]['webmVideoTemplateId'];
            }
        }

        return null;
    }

    /**
     * Get "Poster" url.
     *
     * @param int   $publishPlaceId Publish place id
     * @param array $deployedMedia  Deployed media
     * @param array $configuration  Configuration
     *
     * @return string|null
     */
    protected function getPosterUrl($publishPlaceId, $deployedMedia, $configuration)
    {
        $posterImageTemplateId = $this->getPosterImageTemplateId($publishPlaceId, $configuration);
        foreach ($deployedMedia as $key => $deployedMediaValues) {
            if (substr($deployedMediaValues['remoteFile'], -4) === '.jpg' && $deployedMediaValues['imageTemplateId'] === $posterImageTemplateId) {
                return $deployedMediaValues['remoteFile'];
            }
        }

        return null;
    }

    /**
     * Get "mp4" url.
     *
     * @param int   $publishPlaceId Publish place id
     * @param array $deployedMedia  Deployed media
     * @param array $configuration  Configuration
     *
     * @return string|null
     */
    protected function getMp4VideoUrl($publishPlaceId, $deployedMedia, $configuration)
    {
        $mp4VideoTemplateId = $this->getMp4VideoTemplateId($publishPlaceId, $configuration);
        foreach ($deployedMedia as $key => $deployedMediaValues) {
            if (substr($deployedMediaValues['remoteFile'], -4) === '.mp4' && $deployedMediaValues['videoTemplateId'] === $mp4VideoTemplateId) {
                return $deployedMediaValues['remoteFile'];
            }
        }

        return null;
    }

    /**
     * Get "ogg" url.
     *
     * @param int   $publishPlaceId Publish place id
     * @param array $deployedMedia  Deployed media
     * @param array $configuration  Configuration
     *
     * @return string|null
     */
    protected function getOggVideoUrl($publishPlaceId, $deployedMedia, $configuration)
    {
        $oggVideoTemplateId = $this->getOggVideoTemplateId($publishPlaceId, $configuration);
        foreach ($deployedMedia as $key => $deployedMediaValues) {
            if (substr($deployedMediaValues['remoteFile'], -4) === '.ogv' && $deployedMediaValues['videoTemplateId'] === $oggVideoTemplateId) {
                return $deployedMediaValues['remoteFile'];
            }
        }

        return null;
    }

    /**
     * Get "webm" url.
     *
     * @param int   $publishPlaceId Publish place id
     * @param array $deployedMedia  Deployed media
     * @param array $configuration  Configuration
     *
     * @return string|null
     */
    protected function getWebmVideoUrl($publishPlaceId, $deployedMedia, $configuration)
    {
        $webmVideoTemplateId = $this->getWebmVideoTemplateId($publishPlaceId, $configuration);
        foreach ($deployedMedia as $key => $deployedMediaValues) {
            if (substr($deployedMediaValues['remoteFile'], -4) === '.webm' && $deployedMediaValues['videoTemplateId'] === $webmVideoTemplateId) {
                return $deployedMediaValues['remoteFile'];
            }
        }

        return null;
    }

    /**
     * @return \TYPO3\CMS\Core\Resource\Index\MetaDataRepository
     */
    protected function getMetaDataRepository()
    {
        return GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\Index\MetaDataRepository::class);
    }

    /**
     * Returns an instance of the FileIndexRepository.
     *
     * @return FileIndexRepository
     */
    protected function getFileIndexRepository()
    {
        return FileIndexRepository::getInstance();
    }

    /**
     * Returns the ResourceFactory.
     *
     * @return ResourceFactory
     */
    protected function getResourceFactory()
    {
        return ResourceFactory::getInstance();
    }

    /**
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }
}
