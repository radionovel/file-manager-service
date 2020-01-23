<?php

namespace FileManager\FsObjects;

use FileManager\Interfaces\FsObjectInterface;

/**
 * Class FileObject
 * @package FileManager\FsObjects
 */
class FileObject extends AbstractFsObject implements FsObjectInterface
{
    public const TYPE = 'file';

    /**
     * @return array
     */
    public function info()
    {
        return parent::info() + [
                'extension' => pathinfo($this->getName(), PATHINFO_EXTENSION),
            ];
    }
}
