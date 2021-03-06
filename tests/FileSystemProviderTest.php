<?php

use PHPUnit\Framework\TestCase;
use Radionovel\FileManagerService\Exceptions\CantDeleteException;
use Radionovel\FileManagerService\Exceptions\CreateDirectoryException;
use Radionovel\FileManagerService\Exceptions\DownloaderIsNullException;
use Radionovel\FileManagerService\Exceptions\FileAlreadyExistsException;
use Radionovel\FileManagerService\Exceptions\InvalidPathException;
use Radionovel\FileManagerService\Exceptions\PathNotExistsException;
use Radionovel\FileManagerService\Exceptions\RenameException;
use Radionovel\FileManagerService\Exceptions\UploaderIsNullException;
use Radionovel\FileManagerService\FsObjects\DirectoryObject;
use Radionovel\FileManagerService\FsObjects\FileObject;
use Radionovel\FileManagerService\Interfaces\DownloaderInterface;
use Radionovel\FileManagerService\Interfaces\FsObjectInterface;
use Radionovel\FileManagerService\Interfaces\UploaderInterface;
use Radionovel\FileManagerService\Providers\FileSystemProvider;

class FileSystemProviderTest extends TestCase
{
    /**
     * @var FileSystemProvider
     */
    private $provider;
    /**
     * @var string
     */
    private $base_directory;

    public static function setUpBeforeClass(): void
    {
        static::clear();
        static::init();
    }

    protected static function clear()
    {
        $base_directory = static::getBaseDirectory();

        system(
            sprintf('rm -rf %s', $base_directory)
        );
    }

    public static function getBaseDirectory(): string
    {
        return '/tmp' . DIRECTORY_SEPARATOR . crypt(__CLASS__, 11);
    }

    protected static function init()
    {
        static::mkdir();
        static::mkdir('/folder1');
        static::mkdir('/folder1/test');
        static::mkdir('/folder1/subfolder2');
        static::mkdir('/folder1/some');
        static::mkdir('/folder1/rename-folder');
        static::mkdir('/folder2');

        static::link('/tmp', '/folder1/symlink');

        static::touch('folder1/file1');
        static::touch('folder1/file2');

        static::touch('folder1/move.txt');
        static::touch('folder1/rename.txt');
        static::touch('folder1/copy.txt');
        static::touch('folder1/test/move.txt');
        static::touch('folder1/test/rename.txt');
        static::touch('folder1/test/rename\ \(1\).txt');
        static::touch('folder1/test/rename\ \(2\).txt');

        static::touch('file1');
        static::touch('file2');
    }

    public static function mkdir($path = '')
    {
        $base_directory = static::getBaseDirectory();
        system(
            sprintf('mkdir -p %s%s', $base_directory, $path)
        );
    }

    public static function link($from, $to)
    {
        $base_directory = static::getBaseDirectory();
        system(
            sprintf('ln -s %s %s', $from, $base_directory . $to)
        );
    }

    public static function touch($path)
    {
        $base_directory = static::getBaseDirectory();
        system(
            sprintf('touch %s/%s', $base_directory, $path)
        );
    }

    public function testMoveWithOverwrite()
    {
        $this->provider->move(
            '/folder1/move.txt',
            '/folder1/test',
            true
        );
        $info = $this->provider->getInfo('/folder1/move.txt');
        $this->assertFalse($info);
    }

    public function testMoveWithRename()
    {
        $this->provider->move(
            'folder1/rename.txt',
            'folder1/test',
            false,
            true
        );
        $info = $this->provider->getInfo('/folder1/test/rename (3).txt');
        $this->assertNotFalse($info);
    }

    public function testCopyFile()
    {
        $this->provider->move('folder1/copy.txt', 'folder1/test');
        $info = $this->provider->getInfo('/folder1/test/copy.txt');
        $this->assertNotFalse($info);
    }

    /**
     * @throws CantDeleteException
     * @throws FileAlreadyExistsException
     * @throws InvalidPathException
     * @throws PathNotExistsException
     * @throws RenameException
     */
    public function testCopyDirectory()
    {
        $source = '/folder1/test-copy';
        mkdir($this->base_directory . $source, 0777, true);

        $result = $this->provider->copy($source, '/');
        $directory_exists = is_dir($this->base_directory . '/test-copy');

        $this->assertInstanceOf(DirectoryObject::class, $result);
        $this->assertTrue($directory_exists);
    }

    public function testGetInfo()
    {
        $file_info = $this->provider->getInfo('/file1');
        $this->assertInstanceOf(FileObject::class, $file_info);
    }

    public function testSearch()
    {
        $search_result = $this->provider->search('folder');
        $this->assertIsArray($search_result);
        foreach ($search_result as $item) {
            $this->assertInstanceOf(FsObjectInterface::class, $item);
        }
    }

    public function testProvider()
    {
        $path = $this->provider->getBasePath();
        $this->assertEquals($this->base_directory, $path);
    }

    public function testSanitizePath()
    {
        $path = $this->provider->sanitize('/path/for/test/');
        $this->assertEquals('/path/for/test', $path);
    }

    /**
     * @throws InvalidPathException
     * @throws PathNotExistsException
     */
    public function testListingSuccess()
    {
        $listing = $this->provider->listing('/');
        $this->assertIsArray($listing);
        $this->assertNotEmpty($listing);

        foreach ($listing as $item) {
            $this->assertTrue($item instanceof FsObjectInterface);
            if ($item instanceof FileObject) {
                $info = $item->info();
                $this->assertEquals(FileObject::TYPE, $info['type']);
            } elseif ($item instanceof DirectoryObject) {
                $info = $item->info();
                $this->assertEquals(DirectoryObject::TYPE, $info['type']);
            }
        }
    }

    public function testListingHaventBasepath()
    {
        $listing = $this->provider->listing('/');
        $base_directory = static::getBaseDirectory();
        foreach ($listing as $item) {
            if ($item instanceof FsObjectInterface) {
                $info = $item->info();
                $this->assertStringStartsNotWith($base_directory, $info['path']);
            }
        }
    }

    public function testInvalidPathException()
    {
        $this->expectException(InvalidPathException::class);
        $this->provider->listing('../../');
    }

    public function testPathNotExistsException()
    {
        $this->expectException(PathNotExistsException::class);
        $this->provider->listing('/not/exists/path');
    }

    public function testFileExists()
    {
        $is_exists = $this->provider->exists('/file1');
        $this->assertTrue($is_exists);

        $is_exists = $this->provider->exists('/file_not_exists');
        $this->assertFalse($is_exists);
    }

    public function testInvalidPathExceptionBySymlink()
    {
        $this->expectException(InvalidPathException::class);
        $this->provider->listing('/folder1/symlink');
    }

    /**
     * @dataProvider validDirectoriesProvider
     * @param $actual
     * @param $expected
     * @throws InvalidPathException
     * @throws CreateDirectoryException
     */
    public function testMakeDirectory($actual, $expected)
    {
        $result = $this->provider->mkdir($actual);
        $this->assertTrue($result instanceof FsObjectInterface);
        $directory_exists = is_dir($this->base_directory . $expected);
        $this->assertTrue($directory_exists);
    }

    public function testMakeDirectoryError()
    {
        $this->expectException(InvalidPathException::class);
        $result = $this->provider->mkdir('/../../folder');
        $this->assertFalse($result);
    }

    /**
     * @depends      testMakeDirectory
     * @dataProvider validDirectoriesProvider
     * @param $actual
     * @param $expected
     * @throws InvalidPathException
     * @throws PathNotExistsException
     * @throws CantDeleteException
     */
    public function testDeleteDirectory($actual, $expected)
    {
        $result = $this->provider->delete($actual);
        $this->assertTrue($result);
        $directory_exists = is_dir($this->base_directory . $expected);
        $this->assertFalse($directory_exists);
    }

    /**
     * @throws InvalidPathException
     * @throws PathNotExistsException
     * @throws RenameException
     */
    public function testMoveDirectory()
    {
        $folder = $this->base_directory . '/move-folder/test';
        mkdir($folder, 0777, true);
        $result = $this->provider->move('move-folder/test', '/');
        $this->assertTrue($result instanceof FsObjectInterface);
        $directory_exists = is_dir($this->base_directory . '/test');
        $this->assertTrue($directory_exists);
    }

    /**
     * @throws InvalidPathException
     * @throws PathNotExistsException
     * @throws RenameException
     */
    public function testRenameDirectory()
    {
        $result = $this->provider->rename('/folder1/rename-folder', 'new-name');
        $this->assertTrue($result instanceof FsObjectInterface, 'Вернулся объект FsObjectInterface');
        $directory_exists = is_dir($this->base_directory . '/folder1/new-name');
        $this->assertTrue($directory_exists, 'Папка успешно переименована');
    }

    /**
     * @throws InvalidPathException
     * @throws PathNotExistsException
     * @throws RenameException
     * @depends testRenameDirectory
     */
    public function testRenameAlreadyExistsDirectory()
    {
        $this->expectException(FileAlreadyExistsException::class);
        $this->provider->rename('/folder1/new-name', 'some');
    }

    /**
     * @throws InvalidPathException
     * @throws PathNotExistsException
     * @throws RenameException
     */
    public function testRenameBasePathDirectory()
    {
        $this->expectException(InvalidPathException::class);
        $this->provider->rename('/', 'new-name');
    }

    public function testDownloader()
    {
        $downloader = $this->getMockBuilder(DownloaderInterface::class)
            ->onlyMethods(['download'])
            ->getMock();

        $downloader->expects($this->once())
            ->method('download')
            ->with($this->equalTo([]));

        $this->provider->setDownloader($downloader);
        $this->provider->download([]);
    }

    public function testDownloaderException()
    {
        $this->expectException(DownloaderIsNullException::class);
        $this->provider->download([]);
    }

    public function testUploaderException()
    {
        $this->expectException(UploaderIsNullException::class);
        $this->provider->upload([], '/path');
    }

    public function testUploader()
    {
        $uploader = $this->getMockBuilder(UploaderInterface::class)
            ->onlyMethods(['upload'])
            ->getMock();

        $uploader->expects($this->once())
            ->method('upload')
            ->with($this->equalTo([]), $this->equalTo('/path'));

        $this->provider->setUploader($uploader);
        $this->provider->upload([], '/path');
    }

    public function validDirectoriesProvider()
    {
        return [
            'without slash' => ['my-filder', '/my-filder'],
            'with slash' => ['/my-test-filder', '/my-test-filder']
        ];
    }

    protected function setUp(): void
    {
        $this->base_directory = static::getBaseDirectory();
        $this->provider = new FileSystemProvider($this->base_directory);
        parent::setUp();
    }
}
