<?php

namespace Radionovel\FileManagerService\Traits;

use Radionovel\FileManagerService\Exceptions\PathNotExistsException;

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
        return rtrim($path, '/');
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
