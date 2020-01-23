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

}
