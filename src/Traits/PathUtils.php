<?php

namespace FileManager\Traits;

use FileManager\Exceptions\InvalidPathException;
use FileManager\Exceptions\PathNotExistsException;

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
     * @return string
     * @throws PathNotExistsException
     */
    public function realPath($path)
    {
        $real_path = realpath($path);
        if ($real_path === false) {
            throw new PathNotExistsException(
                sprintf('Path %s not exists', $path)
            );
        }
        return $real_path;
    }
}
