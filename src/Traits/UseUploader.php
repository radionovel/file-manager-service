<?php
namespace FileManager\Traits;

use FileManager\Exceptions\UploaderIsNullException;
use FileManager\Interfaces\UploaderInterface;

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
     * @return mixed
     * @throws UploaderIsNullException
     */
    public function upload($files)
    {
        if (!is_null($this->uploader)) {
            return $this->uploader->upload($files);
        }
        throw new UploaderIsNullException('Uploader cant be null');
    }
}
