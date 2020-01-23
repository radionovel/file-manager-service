<?php
namespace FileManager\Traits;

use FileManager\Interfaces\DownloaderInterface;
use FileManager\Exceptions\DownloaderIsNullException;

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
     * @return mixed
     * @throws DownloaderIsNullException
     */
    public function download($file)
    {
        if (!is_null($this->downloader)) {
            return $this->downloader->download($file);
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
