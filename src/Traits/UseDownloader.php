<?php
namespace Radionovel\FileManagerService\Traits;

use Radionovel\FileManagerService\Interfaces\DownloaderInterface;
use Radionovel\FileManagerService\Exceptions\DownloaderIsNullException;

/**
 * Trait UseDownloader
 * @package FileManager\Traits
 */
trait UseDownloader
{
    /**
     * @var DownloaderInterface
     */
    private $downloader;

    /**
     * @param $file
     * @param $callback
     * @return mixed
     * @throws DownloaderIsNullException
     */
    public function download($file, $callback = null)
    {
        if (!is_null($this->downloader)) {
            return $this->downloader->download($file, $callback);
        }
        throw new DownloaderIsNullException('Downloader cant be null');
    }

    /**
     * @param DownloaderInterface $downloader
     */
    public function setDownloader(DownloaderInterface $downloader)
    {
        $this->downloader = $downloader;
    }
}
