<?php

namespace Radionovel\FileManagerService\Providers;

use Radionovel\FileManagerService\Exceptions\InvalidPathException;
use Radionovel\FileManagerService\Exceptions\PathNotExistsException;
use Radionovel\FileManagerService\FsObjects\DirectoryObject;
use Radionovel\FileManagerService\FsObjects\FileObject;
use Radionovel\FileManagerService\Interfaces\FileSystemProviderInterface;
use Radionovel\FileManagerService\Traits\PathUtils;
use Radionovel\FileManagerService\Traits\UseDownloader;
use Radionovel\FileManagerService\Traits\UseUploader;

/**
 * Class FileSystemProvider
 * @package Providers
 */
class FileSystemProvider implements FileSystemProviderInterface
{
    use PathUtils, UseDownloader, UseUploader;
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
     * @throws PathNotExistsException
     * @throws InvalidPathException
     */
    protected function getValidPath($path)
    {
        $path = $this->makeFullPath($path);
        $real_path = $this->realPath($path);
        if (strpos($real_path, $this->getBasePath()) !== 0) {
            throw new InvalidPathException(
                sprintf('Path %s is not valid', $real_path)
            );
        }
        return $real_path;
    }

    /**
     * @param $path
     * @return string
     * @throws InvalidPathException
     */
    private function makeFullPath($path)
    {
        $full_path = $this->getBasePath() . DIRECTORY_SEPARATOR . $this->sanitize($path);
        if (strpos($full_path, '/..') !== false) {
            throw new InvalidPathException();
        }
        return $full_path;
    }

    /**
     * @param $path
     * @return false|string
     */
    private function extractRelativePath($path)
    {
        if (strpos($path, $this->getBasePath()) === 0) {
            return substr($path, strlen($this->getBasePath()));
        }
        return $path;
    }

    /**
     * @param $path
     * @return array
     * @throws PathNotExistsException
     * @throws InvalidPathException
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
            $item_path = $this->extractRelativePath($path . DIRECTORY_SEPARATOR . $item);
            if (is_dir($item_path)) {
                $result[] = new DirectoryObject($item_path);
            } else {
                $result[] = new FileObject($item_path);
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
        try {
            $path = $this->makeFullPath($path);
        } catch (InvalidPathException $exception) {
            return false;
        }
        $command = escapeshellcmd(
            sprintf('mkdir -p %s', $path)
        );
        system($command, $exit_code);
        return $exit_code === 0;

    }

    /**
     * @param $path
     * @return bool
     * @throws InvalidPathException
     * @throws PathNotExistsException
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
     * @throws InvalidPathException
     * @throws PathNotExistsException
     */
    public function move($source, $destination)
    {
        $source = $this->getValidPath($source);
        $destination = $this->getValidPath($destination);
        $destination = $destination . DIRECTORY_SEPARATOR . basename($source);
        return rename($source, $destination);
    }

    /**
     * @param $path
     * @param $new_name
     * @return bool
     * @throws InvalidPathException
     * @throws PathNotExistsException
     */
    public function rename($path, $new_name)
    {
        $source = $this->getValidPath($path);
        $path_array = explode(DIRECTORY_SEPARATOR, $source);
        array_pop($path_array);
        array_push($path_array, basename($new_name));
        $destination = implode(DIRECTORY_SEPARATOR, $path_array);
        return rename($source, $destination);
    }

    /**
     * @param $path
     * @return bool
     */
    public function exists($path)
    {
        try {
            $this->getValidPath($path);
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }
}
