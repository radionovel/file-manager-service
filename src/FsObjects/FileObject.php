<?php

namespace FileManager\FsObjects;

/**
 * Class FileObject
 * @package FileManager\FsObjects
 */
class FileObject implements FsObjectInterface {
    /**
     * @var string
     */
    private $name;

    /**
     * FileObject constructor.
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed|null
     */
    public function info()
    {
        // TODO: Implement info() method.
    }
}
