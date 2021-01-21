<?php
namespace Radionovel\FileManagerService\Filters;

/**
 * Interface FilterInterface
 */
interface FilterInterface {
    public function filtered($directory, $file_name);
}
