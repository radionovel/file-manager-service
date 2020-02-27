<?php
namespace Radionovel\FileManagerService\FsObjects;

use Radionovel\FileManagerService\Exceptions\PathNotExistsException;

/**
 * Class FileObjectFactory
 */
class FileObjectFactory
{
    /**
     * @param $absolute_path
     * @param $relative_path
     * @return DirectoryObject|FileObject
     * @throws PathNotExistsException
     */
    public static function make($absolute_path, $relative_path)
    {
        if (is_dir($absolute_path)) {
            return new DirectoryObject($relative_path);
        } else if (file_exists($absolute_path)) {
            return new FileObject($relative_path, filesize($absolute_path));
        }
        throw new PathNotExistsException(sprintf('Path %s not exists', $relative_path));
    }
}
