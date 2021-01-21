<?php

namespace Radionovel\FileManagerService\Filters;

class FilterFactory
{
    /**
     * @param $params
     * @return Filter
     */
    public static function create($params)
    {
        return new Filter($params);
    }
}