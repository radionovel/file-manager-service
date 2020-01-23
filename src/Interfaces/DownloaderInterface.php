<?php

namespace Radionovel\FileManagerService\Interfaces;

/**
 * Interface DownloaderInterface
 * @package FileManager
 */
interface DownloaderInterface
{
    /**
     * @param $file
     * @return mixed
     */
    public function download($file);
}