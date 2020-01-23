<?php

namespace FileManager\Interfaces;

/**
 * Interface ProviderInterface
 * @package FileManager\Providers
 */
interface FileSystemProviderInterface
{

    /**
     * Download file thought external downloader
     * @param $file
     * @return mixed
     */
    public function download($file);

    /**
     * Upload filer thought external uploader
     * @param $files
     * @return mixed
     */
    public function upload($files);

    /**
     * Create directory
     * @param $path
     * @return mixed
     */
    public function mkdir($path);

    /**
     * Delete directory or file
     * @param $path
     * @return mixed
     */
    public function delete($path);

    /**
     * Move directory or file
     * @param $source
     * @param $destination
     * @return mixed
     */
    public function move($source, $destination);

    /**
     * Get listing of directory
     * @param $path
     * @return array
     */
    public function listing($path);
}
