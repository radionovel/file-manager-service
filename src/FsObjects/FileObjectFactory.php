<?php
namespace Radionovel\FileManagerService\FsObjects;

/**
 * Class FileObjectFactory
 */
class FileObjectFactory
{
    /**
     * @param $absolute_path
     * @param $relative_path
     * @return DirectoryObject|FileObject
     */
    public static function make($absolute_path, $relative_path)
    {
        if (is_dir($absolute_path)) {
            return new DirectoryObject($relative_path);
        }

        return new FileObject($relative_path, filesize($absolute_path));
    }
}
