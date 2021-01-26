<?php

namespace Radionovel\FileManagerService\Providers;

use Exception;
use Radionovel\FileManagerService\Exceptions\CantDeleteException;
use Radionovel\FileManagerService\Exceptions\CreateDirectoryException;
use Radionovel\FileManagerService\Exceptions\DownloaderIsNullException;
use Radionovel\FileManagerService\Exceptions\FileAlreadyExistsException;
use Radionovel\FileManagerService\Exceptions\InvalidPathException;
use Radionovel\FileManagerService\Exceptions\PathNotExistsException;
use Radionovel\FileManagerService\Exceptions\RenameException;
use Radionovel\FileManagerService\Exceptions\UploaderIsNullException;
use Radionovel\FileManagerService\Filters\FilterFactory;
use Radionovel\FileManagerService\Filters\FilterInterface;
use Radionovel\FileManagerService\FsObjects\DirectoryObject;
use Radionovel\FileManagerService\FsObjects\FileObject;
use Radionovel\FileManagerService\FsObjects\FileObjectFactory;
use Radionovel\FileManagerService\Interfaces\FileSystemProviderInterface;
use Radionovel\FileManagerService\Interfaces\FsObjectInterface;
use Radionovel\FileManagerService\Traits\PathUtils;
use Radionovel\FileManagerService\Traits\UseDownloader;
use Radionovel\FileManagerService\Traits\UseUploader;
use RuntimeException;

/**
 * Class FileSystemProvider
 *
 * @package Providers
 */
class FileSystemProvider implements FileSystemProviderInterface
{
    use PathUtils, UseDownloader, UseUploader;

    const MOVE_OPERATION_NONE = 1;
    const MOVE_OPERATION_RENAME = 2;
    const MOVE_OPERATION_OVERWRITE = 3;

    /**
     * @var string
     */
    private $basePath;

    /**
     * FileSystemProvider constructor.
     *
     * @param $base_path
     */
    public function __construct($base_path)
    {
        $this->basePath = $this->sanitize($base_path);
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
     *
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
                sprintf('Path %s is not valid. Current base path %s', $real_path, $this->getBasePath())
            );
        }
        return $real_path;
    }

    /**
     * @param $path
     *
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
     *
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
     *
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
            $item = FileObjectFactory::make($full_path, $item_path);
            $item->setModifyTime(filemtime($full_path));
            $result[] = $item;
        }
        return $result;
    }

    /**
     * @param $path
     *
     * @return bool|DirectoryObject|FileObject
     */
    public function getInfo($path)
    {
        try {
            $path = $this->getValidPath($path);
            $item_path = $this->extractRelativePath($path);
            $item = FileObjectFactory::make($path, $item_path);
            $item->setModifyTime(filemtime($path));
            return $item;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param $path
     *
     * @return bool
     * @throws FileAlreadyExistsException
     */
    protected function checkEmptyPath($path)
    {
        try {
            $this->realPath($path);
        } catch (Exception $exception) {
            return true;
        }
        throw new FileAlreadyExistsException();
    }

    /**
     * @param $path
     *
     * @return DirectoryObject
     * @throws CreateDirectoryException
     * @throws FileAlreadyExistsException
     * @throws InvalidPathException
     * @throws PathNotExistsException
     */
    public function mkdir($path)
    {
        $path = $this->makeFullPath($path);
        $this->checkEmptyPath($path);
        $relative_path = $this->extractRelativePath($path);
        if (@mkdir($path)) {
            return $result[] = FileObjectFactory::make($path, $relative_path);
        }

        throw new CreateDirectoryException(
            sprintf('Cant create directory with path: %s', $relative_path)
        );
    }

    /**
     * @param $path
     *
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
                if (!$this->delete($delete_path)) {
                    throw new CantDeleteException();
                }
            }
            return rmdir($path);
        }
        return unlink($path);
    }


    private function createFileObject($directory, $file_name)
    {
        $full_path = $directory . DIRECTORY_SEPARATOR . $file_name;
        $item_path = $this->extractRelativePath($full_path);
        return FileObjectFactory::make($full_path, $item_path);
    }

    /**
     * @param string|FilterInterface $filter
     * @param string $directory
     *
     * @return array
     * @throws InvalidPathException
     * @throws PathNotExistsException
     */
    public function search($query, $path = '/')
    {
        try {
            $path = $this->getValidPath($path);
        } catch (Exception $exception) {
            return [];
        }

        if (!is_dir($path)) {
            return [];
        }

        if (is_string($query)) {
            $filter = FilterFactory::create(['name' => $query]);
        } elseif ($query instanceof FilterInterface) {
            $filter = $query;
        } else {
            throw new RuntimeException();
        }

        $result = [];
        $files = array_diff(scandir($path), array('.', '..'));
        foreach ($files as $file) {
            $full_path = $path . DIRECTORY_SEPARATOR . $file;
            $item_path = $this->extractRelativePath($full_path);
            if ($filter->filtered($full_path)) {
                $result[] = FileObjectFactory::make($full_path, $item_path);
            }
            $result = array_merge($result, $this->search($query, $item_path));
        }

        return $result;
    }

    /**
     * @param     $source
     * @param     $destination
     * @param int $move_operation
     *
     * @return FsObjectInterface
     * @throws CantDeleteException
     * @throws FileAlreadyExistsException
     * @throws InvalidPathException
     * @throws PathNotExistsException
     * @throws RenameException
     */
    public function move($source, $destination, $move_operation = self::MOVE_OPERATION_NONE)
    {
        $source = $this->getValidPath($source);
        $destination = $this->getValidPath($destination);
        $destination = $destination . DIRECTORY_SEPARATOR . basename($source);
        return $this->renameObject($source, $destination, $move_operation);
    }

    /**
     * @param     $source
     * @param     $destination
     * @param int $move_operation
     *
     * @return DirectoryObject|FileObject
     * @throws CantDeleteException
     * @throws FileAlreadyExistsException
     * @throws InvalidPathException
     * @throws PathNotExistsException
     */
    public function copy($source, $destination, $move_operation = self::MOVE_OPERATION_NONE)
    {
        $source = $this->getValidPath($source);
        $destination = $this->getValidPath($destination);
        $destination = $destination . DIRECTORY_SEPARATOR . basename($source);
        return $this->copyObject($source, $destination, $move_operation);
    }

    /**
     * @param $source
     * @param $destination
     * @param $move_operation
     *
     * @return DirectoryObject|FileObject
     * @throws CantDeleteException
     * @throws FileAlreadyExistsException
     * @throws InvalidPathException
     * @throws PathNotExistsException
     * @throws RenameException
     */
    protected function renameObject($source, $destination, $move_operation)
    {
        if ($this->realPath($source) === $this->getBasePath()) {
            throw new InvalidPathException('Cant rename or move root directory');
        }

        if ($source === $destination) {
            $relative_path = $this->extractRelativePath($destination);
            return FileObjectFactory::make($destination, $relative_path);
        }

        if (is_dir($source) && is_dir($destination) && strpos($this->realPath($destination), $this->realPath($source)) !== false) {
            throw new InvalidPathException('Cant rename or move');
        }

        try {
            $this->checkEmptyPath($destination);
        } catch (FileAlreadyExistsException $ex) {
            if ($move_operation === self::MOVE_OPERATION_OVERWRITE) {
                $this->delete($this->extractRelativePath($destination));
            } else if ($move_operation === self::MOVE_OPERATION_RENAME) {
                $destination = $this->makeUniqueName($destination);
            } else {
                throw new FileAlreadyExistsException();
            }
        }

        $relative_path = $this->extractRelativePath($destination);
        if (! rename($source, $destination)) {
            throw new RenameException(
                sprintf('Cant rename file or directory: %s', $relative_path)
            );
        }
        return FileObjectFactory::make($destination, $relative_path);
    }

    /**
     * @param $source
     * @param $destination
     * @param $move_operation
     *
     * @return DirectoryObject|FileObject
     * @throws CantDeleteException
     * @throws FileAlreadyExistsException
     * @throws InvalidPathException
     * @throws PathNotExistsException
     */
    protected function copyObject($source, $destination, $move_operation)
    {
        if ($this->realPath($source) === $this->getBasePath()) {
            throw new InvalidPathException('Cant copy root directory');
        }

        if ($destination === $this->realPath($source)) {
            $move_operation = self::MOVE_OPERATION_RENAME;
        } else if (is_dir($source) && strpos($destination, $this->realPath($source)) !== false) {
            throw new InvalidPathException('Cant rename or move');
        }

        try {
            $this->checkEmptyPath($destination);
        } catch (FileAlreadyExistsException $ex) {
            if ($move_operation === self::MOVE_OPERATION_OVERWRITE) {
                $this->delete($this->extractRelativePath($destination));
            } else if ($move_operation === self::MOVE_OPERATION_RENAME) {
                $destination = $this->makeUniqueName($destination);
            } else {
                throw new FileAlreadyExistsException();
            }
        }

        $relative_path = $this->extractRelativePath($destination);
        $this->copyRecursive($source, $destination);
        return FileObjectFactory::make($destination, $relative_path);
    }

    /**
     * @param $source
     * @param $destination
     */
    private function copyRecursive($source, $destination)
    {
        if (is_dir($source)) {
            @mkdir($destination);
            $files = scandir($source);
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    $this->copyRecursive("$source/$file", "$destination/$file");
                }
            }
        } elseif (file_exists($source)) {
            copy($source, $destination);
        }
    }

    /**
     * @param     $path
     * @param int $attempt
     *
     * @return mixed
     */
    private function makeUniqueName($path, $attempt = 0)
    {
        try {
            if ($attempt > 0) {
                $path_info = pathinfo($path);
                $extension = isset($path_info['extension']) && strlen($path_info['extension']) > 0 ? '.' . $path_info['extension'] : '';
                $check_path = sprintf('%s%s%s (%s)%s',
                    $path_info['dirname'], DIRECTORY_SEPARATOR, $path_info['filename'], $attempt, $extension);

            } else {
                $check_path = $path;
            }
            $this->checkEmptyPath($check_path);
        } catch (FileAlreadyExistsException $exception) {
            return $this->makeUniqueName($path, $attempt + 1);
        }
        return $check_path;
    }

    /**
     * @param $path
     * @param $new_name
     *
     * @return DirectoryObject|FileObject
     * @throws CantDeleteException
     * @throws FileAlreadyExistsException
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
        return $this->renameObject($source, $destination, self::MOVE_OPERATION_NONE);
    }

    /**
     * @param $path
     *
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
     * @param      $file
     * @param null $callback
     *
     * @return mixed
     * @throws DownloaderIsNullException
     * @throws InvalidPathException
     * @throws PathNotExistsException
     */
    public function safeDownload($file, $callback = null)
    {
        $file = $this->getValidPath($file);
        return $this->download($file, $callback);
    }

    /**
     * @param      $files
     * @param      $destination
     * @param null $callback
     *
     * @return mixed
     * @throws InvalidPathException
     * @throws PathNotExistsException
     * @throws UploaderIsNullException
     */
    public function safeUpload($files, $destination, $callback = null)
    {
        $destination = $this->getValidPath($destination);
        return $this->upload($files, $destination, $callback);
    }

    /**
     * @param string $target
     *
     * @return bool
     * @throws InvalidPathException
     * @throws PathNotExistsException
     */
    public function isDirectory(string $target)
    {
        return is_dir($this->getValidPath($target));
    }
}
