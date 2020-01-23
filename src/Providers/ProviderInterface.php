<?php

namespace FileManager\Providers;

/**
 * Interface ProviderInterface
 * @package FileManager\Providers
 */
interface ProviderInterface
{
//    public function download($item);
//    public function upload($item_fs);
//    public function extract($path);

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
     * Check directory has one files
     * @param $path
     * @return mixed
     */
    public function is_empty($path);

    /**
     * Move directory or file
     * @param $source
     * @param $destination
     * @return mixed
     */
    public function move($source, $destination);

    /**
     * Search files and directories
     * @param $query
     * @return mixed
     */
    public function search($query);

    /**
     * Get listing of directory
     * @param $path
     * @return array
     */
    public function listing($path);
}
