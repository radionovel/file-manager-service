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
        $path = realpath($path);
        if ($path === false) {
            throw new PathNotExistsException(
                sprintf('Path %s not exists', $path)
            );
        }
        return $path;
    }
}
