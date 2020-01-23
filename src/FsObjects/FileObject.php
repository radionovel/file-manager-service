<?php

namespace Radionovel\FileManagerService\FsObjects;

use Radionovel\FileManagerService\Interfaces\FsObjectInterface;

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
