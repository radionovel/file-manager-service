<?php

namespace FileManager\Traits;

/**
 * Trait PathUtils
 * @package FileManager\Traits
 */
trait PathUtils {

    /**
     * @param $path
     * @return string
     */
    public function sanitize($path)
    {
        return trim($path, '/');
    }

    /**
     * @param $path
     * @return false|\RuntimeException|string
     */
    public function realPath($path)
    {
        $path = realpath($path);
        if ($path === false) {
            return new \RuntimeException('Path not exists');
        }
        return $path;
    }
}
