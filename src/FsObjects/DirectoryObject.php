<?php

namespace Radionovel\FileManagerService\FsObjects;

use Radionovel\FileManagerService\Interfaces\FsObjectInterface;

/**
 * Class DirectoryObject
 * @package FileManager\FsObjects
 */
class DirectoryObject extends AbstractFsObject implements FsObjectInterface
{
    public const TYPE = 'directory';
}
