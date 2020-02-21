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

    protected $size = 0;
    private $sizeFormat = '%s %s';

    /**
     * FileObject constructor.
     * @param string $path
     * @param int $size
     */
    public function __construct($path, $size = 0)
    {
        $this->size = $size;
        parent::__construct($path);
    }

    /**
     * @return string
     */
    protected function formatSize() {
        $measure_list = ['B', 'KB', 'MB', 'GB', 'TB'];
        $measure_key = 0;
        $size = $this->size;
        while ($size > 1000) {
            if (!isset($measure_list[$measure_key])) {
                break;
            }
            $size = $size / 1024;
            $measure_key++;
        }
        return sprintf($this->sizeFormat, round($size, 2), $measure_list[$measure_key]);
    }

    /**
     * @return array
     */
    public function info()
    {
        return parent::info() + [
                'extension' => pathinfo($this->getName(), PATHINFO_EXTENSION),
                'size' => $this->size,
                'formatSize' => $this->formatSize(),
            ];
    }
}
