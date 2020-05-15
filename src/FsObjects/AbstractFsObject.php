<?php

namespace Radionovel\FileManagerService\FsObjects;

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
     * @var int
     */
    protected $size = 0;

    /**
     * @var int
     */
    protected $modifyTime;

    /**
     * AbstractFsObject constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->name = basename($path);
        $this->path = $path;
        $this->modifyTime = 0;
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
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getModifyTime(): int
    {
        return $this->modifyTime;
    }

    /**
     * @return array
     */
    public function info() {
        return [
            'name' => $this->getName(),
            'path' => $this->getPath(),
            'type' => static::TYPE,
            'modify_time' => $this->getModifyTime()
        ];
    }

    /**
     * @param int $modifyTime
     */
    public function setModifyTime ($modifyTime): void
    {
        $this->modifyTime = $modifyTime;
    }
}
