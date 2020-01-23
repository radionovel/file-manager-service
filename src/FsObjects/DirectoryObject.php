<?php

namespace FileManager\FsObjects;

/**
 * Class DirectoryObject
 * @package FileManager\FsObjects
 */
class DirectoryObject implements FsObjectInterface {
    /**
     * @var string
     */
    private $name;

    /**
     * DirectoryObject constructor.
     * @param $name
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
