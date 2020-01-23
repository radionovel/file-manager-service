<?php


namespace Radionovel\FileManagerService\Interfaces;

/**
 * Class UploaderInterface
 * @package FileManager\Traits
 */
interface UploaderInterface
{
    /**
     * @param $files
     * @return mixed
     */
    public function upload($files);
}