<?php

namespace FileManager\Providers;

use FileManager\FsObjects\DirectoryObject;
use FileManager\FsObjects\FileObject;
use FileManager\Traits\PathUtils;

/**
 * Class FileSystemProvider
 * @package Providers
 */
class FileSystemProvider
{
    use PathUtils;
    /**
     * @var string
     */
    private $basePath;

    /**
     * FileSystemProvider constructor.
     * @param $base_path
     */
    public function __construct($base_path)
    {
        $this->basePath = DIRECTORY_SEPARATOR . $this->sanitize($base_path);
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @param $path
     * @return string
     */
    public function getValidPath($path)
    {
        $path = $this->getBasePath() . DIRECTORY_SEPARATOR . $this->sanitize($path);
        $path = $this->realPath($path);

        if ($path === false || strpos($path, $this->getBasePath()) !== 0) {
            throw new \RuntimeException('Error');
        }

        return $path;
    }

    /**
     * @param $path
     * @return array
     */
    public function listing($path)
    {
        $path = $this->getValidPath($path);
        $items = scandir($path);
        $result = [];
        foreach ($items as $item) {

            if (in_array($item, ['.', '..'])) {
                continue;
            }
            $item_path = $path . DIRECTORY_SEPARATOR . $item;
            if (is_dir($item_path)) {
                $result[] = new DirectoryObject($item);
            } else {
                $result[] = new FileObject($item);
            }
        }
        return $result;
    }

    /**
     * @param $path
     * @return bool
     */
    public function mkdir($path)
    {
        $path = $this->getValidPath($path);
        $command = escapeshellcmd(
            sprintf('mkdir -p %s', $path)
        );
        system($command, $exit_code);
        return $exit_code === 0;
    }

    /**
     * @param $path
     * @return bool
     */
    public function delete($path)
    {
        $path = $this->getValidPath($path);
        $command = escapeshellcmd(
            sprintf('rm -rf %s', $path)
        );
        system($command, $exit_code);
        return $exit_code === 0;
    }

    /**
     * @param $source
     * @param $destination
     * @return bool
     */
    public function move($source, $destination)
    {
        $source = $this->getValidPath($source);
        $destination = $this->getValidPath($destination);
        $command = escapeshellcmd(
            sprintf('mv -f %s %s', $source, $destination)
        );
        system($command, $exit_code);
        return $exit_code === 0;
    }

    /**
     * @param $path
     * @return bool
     */
    public function exists($path)
    {
        $path = $this->getValidPath($path);
        return is_dir($path) || file_exists($path);
    }
}
