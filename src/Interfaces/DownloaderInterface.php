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
     * @param $callback
     * @return mixed
     */
    public function download($file, $callback = null);
}