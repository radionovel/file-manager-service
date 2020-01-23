<?php

namespace FileManager\FsObjects;

use FileManager\Interfaces\FsObjectInterface;

/**
 * Class DirectoryObject
 * @package FileManager\FsObjects
 */
class DirectoryObject extends AbstractFsObject implements FsObjectInterface
{
    public const TYPE = 'directory';
}
