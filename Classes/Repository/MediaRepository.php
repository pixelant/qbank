<?php

declare(strict_types=1);


namespace Pixelant\Qbank\Repository;


use QBNK\QBank\API\Model\MediaResponse;
use TYPO3\CMS\Core\Resource\Folder;

class MediaRepository extends AbstractRepository
{
    /**
     * @var array<MediaResponse> Key is the media ID
     */
    protected static $mediaCache = [];

    /**
     * Retrieve a media item.
     *
     * @param int $id
     * @return MediaResponse
     */
    public function findById(int $id): MediaResponse
    {
        if (isset(self::$mediaCache[$id])) {
            return self::$mediaCache[$id];
        }

        self::$mediaCache[$id] = $this->api->media()->retrieveMedia($id);

        return self::$mediaCache[$id];
    }

    /**
     * Downloads media and returns a reference to the file as a resource.
     *
     * @param int $id
     * @return resource
     */
    public function downloadById(int $id, Folder $targetFolder)
    {
        return $this->api->media()->download($id);
    }
}
