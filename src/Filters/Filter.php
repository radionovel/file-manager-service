<?php

namespace Radionovel\FileManagerService\Filters;

/**
 * Class Filter
 * @package Radionovel\FileManagerService\Filters
 */
class Filter implements FilterInterface
{
    /**
     * @var array
     */
    protected $filters = [];

    /**
     * Filter constructor.
     * @param $params
     */
    public function __construct($params)
    {
        $this->filters = $params;
    }

    /**
     * @param $path
     * @return bool
     */
    public function filtered($path): bool
    {
        if (empty($this->filters)) {
            return true;
        }

        return $this->filterName($path) && $this->filterExtension($path);
    }

    /**
     * @param $path
     * @return bool
     */
    private function filterName($path): bool
    {
        if (!isset($this->filters['name']) || trim($this->filters['name']) === '') {
            return true;
        }

        $base_name = pathinfo($path, PATHINFO_BASENAME);
        return strpos($base_name, $this->filters['name']) !== false;
    }

    /**
     * @param $path
     * @return bool
     */
    private function filterExtension($path): bool
    {
        if (!isset($this->filters['extensions'])) {
            return true;
        }

        if (is_string($this->filters['extensions']) && trim($this->filters['extensions']) !== '') {
            $this->filters['extensions'] = [
                $this->filters['extensions']
            ];
        }
        $file_extension = pathinfo($path, PATHINFO_EXTENSION);
        foreach ($this->filters['extensions'] as $test_extension) {
            if ($file_extension === $test_extension) {
                return true;
            }
        }
        return false;
    }
}
