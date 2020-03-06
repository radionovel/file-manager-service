<?php
namespace Radionovel\FileManagerService\Traits;

use Radionovel\FileManagerService\Exceptions\UploaderIsNullException;
use Radionovel\FileManagerService\Interfaces\UploaderInterface;

/**
 * Trait UseUploader
 * @package FileManager\Traits
 */
trait UseUploader
{
    /**
     * @var UploaderInterface
     */
    private $uploader;

    /**
     * @param UploaderInterface $uploader
     */
    public function setUploader(UploaderInterface $uploader)
    {
        $this->uploader = $uploader;
    }

    /**
     * @param $files
     * @param $destination
     * @param $callback
     * @return mixed
     * @throws UploaderIsNullException
     */
    public function upload($files, $destination, $callback = null)
    {
        if (!is_null($this->uploader)) {
            return $this->uploader->upload($files, $destination, $callback);
        }
        throw new UploaderIsNullException('Uploader cant be null');
    }
}
