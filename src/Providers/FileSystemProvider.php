<?php

namespace Radionovel\FileManagerService\Providers;

use Exception;
use Radionovel\FileManagerService\Exceptions\CantDeleteException;
use Radionovel\FileManagerService\Exceptions\CreateDirectoryException;
use Radionovel\FileManagerService\Exceptions\DownloaderIsNullException;
use Radionovel\FileManagerService\Exceptions\InvalidPathException;
use Radionovel\FileManagerService\Exceptions\PathNotExistsException;
use Radionovel\FileManagerService\Exceptions\RenameException;
use Radionovel\FileManagerService\FsObjects\DirectoryObject;
use Radionovel\FileManagerService\FsObjects\FileObject;
use Radionovel\FileManagerService\Interfaces\FileSystemProviderInterface;
use Radionovel\FileManagerService\Interfaces\FsObjectInterface;
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
            $full_path = $path . DIRECTORY_SEPARATOR . $item;
            $item_path = $this->extractRelativePath($full_path);
            if (is_dir($full_path)) {
                $result[] = new DirectoryObject($item_path);
            } else {
                $result[] = new FileObject($item_path, filesize($full_path));
            }
        }
        return $result;
    }

    /**
     * @param $path
     * @return DirectoryObject
     * @throws InvalidPathException
     * @throws CreateDirectoryException
     */
    public function mkdir($path)
    {
        $path = $this->makeFullPath($path);
        $relative_path = $this->extractRelativePath($path);
        if (mkdir($path)) {
            return new DirectoryObject($relative_path);
        }

        throw new CreateDirectoryException(
            sprintf('Cant create directory with path: %s', $relative_path)
        );
    }

    /**
     * @param $path
     * @return bool
     * @throws InvalidPathException
     * @throws PathNotExistsException
     * @throws CantDeleteException
     */
    public function delete($path)
    {
        $path = $this->getValidPath($path);
        if (is_dir($path)) {
            $files = array_diff(scandir($path), array('.', '..'));
            foreach ($files as $file) {
                $delete_path = $this->extractRelativePath("$path/$file");
                if (! $this->delete($delete_path)) {
                    throw new CantDeleteException();
                }
            }
            return rmdir($path);
        }
        return unlink($path);
    }

    /**
     * @param $source
     * @param $destination
     * @return FsObjectInterface
     * @throws InvalidPathException
     * @throws PathNotExistsException
     * @throws RenameException
     */
    public function move($source, $destination)
    {
        $source = $this->getValidPath($source);
        $destination = $this->getValidPath($destination);
        $destination = $destination . DIRECTORY_SEPARATOR . basename($source);
        return $this->renameObject($source, $destination);
    }

    /**
     * @param $source
     * @param $destination
     * @return DirectoryObject|FileObject
     * @throws RenameException
     */
    protected function renameObject($source, $destination)
    {
        $relative_path = $this->extractRelativePath($destination);
        if (! rename($source, $destination)) {
            throw new RenameException(
                sprintf('Cant rename file or directory: %s', $relative_path)
            );
        }
        return is_dir($destination)
            ? new DirectoryObject($relative_path)
            : new FileObject($relative_path);
    }

    /**
     * @param $path
     * @param $new_name
     * @return DirectoryObject|FileObject
     * @throws InvalidPathException
     * @throws PathNotExistsException
     * @throws RenameException
     */
    public function rename($path, $new_name)
    {
        $source = $this->getValidPath($path);
        $path_array = explode(DIRECTORY_SEPARATOR, $source);
        array_pop($path_array);
        array_push($path_array, basename($new_name));
        $destination = implode(DIRECTORY_SEPARATOR, $path_array);
        return $this->renameObject($source, $destination);
    }

    /**
     * @param $path
     * @return bool
     */
    public function exists($path)
    {
        try {
            $this->getValidPath($path);
        } catch (Exception $exception) {
            return false;
        }
        return true;
    }

    /**
     * @param $file
     * @return mixed
     * @throws InvalidPathException
     * @throws PathNotExistsException
     * @throws DownloaderIsNullException
     */
    public function safeDownload($file)
    {
        $file = $this->getValidPath($file);
        return $this->download($file);
    }

    /**
     * @param $files
     * @param $destination
     * @return mixed
     * @throws InvalidPathException
     * @throws PathNotExistsException
     * @throws \Radionovel\FileManagerService\Exceptions\UploaderIsNullException
     */
    public function safeUpload($files, $destination)
    {
        $destination = $this->getValidPath($destination);
        return $this->upload($files, $destination);
    }
}
