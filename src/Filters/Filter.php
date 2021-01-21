<?php

namespace Radionovel\FileManagerService\Filters;

/**
 * Interface FilterInterface
 */
class Filter implements FilterInterface
{

    protected $filters = [];

    /**
     * Filter constructor.
     *
     * @param $filters
     */
    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    /**
     * @param $directory
     * @param $file_name
     *
     * @return bool
     */
    public function filtered($directory, $file_name)
    {
        if (empty($this->filters)) {
            return true;
        }

        if (! $this->filterByName($file_name)) {
            return false;
        }

        return false;
    }

    /**
     * @param $file_name
     *
     * @return false
     */
    private function filterByName($file_name)
    {
        if (! isset($this->filters['name'])) {
            return true;
        }

        return strlen($this->filters['name']) === 0 ? true : strpos($file_name, $this->filters['name']) === false;

    }

    private function filterExtension($file_name)
    {
        return true;
    }
}
