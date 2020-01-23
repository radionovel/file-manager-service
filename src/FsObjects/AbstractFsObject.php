<?php

namespace FileManager\FsObjects;

/**
 * Class AbstractFsObject
 * @package FileManager\FsObjects
 */
class AbstractFsObject
{
    public const TYPE = 'unknown';

    /**
     * @var string
     */
    protected $name;
    /**
     * @var
     */
    protected $path;

    /**
     * AbstractFsObject constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->name = basename($path);
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }


    /**
     * @return array
     */
    public function info()
    {
        return [
            'name' => $this->getName(),
            'path' => $this->getPath(),
            'type' => static::TYPE
        ];
    }
}
