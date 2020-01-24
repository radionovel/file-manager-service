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
     * @param $destination
     * @return mixed
     */
    public function upload($files, $destination);
}