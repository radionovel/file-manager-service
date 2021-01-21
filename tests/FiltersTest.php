<?php

use PHPUnit\Framework\TestCase;
use Radionovel\FileManagerService\Filters\Filter;

class FiltersTest extends TestCase
{
    /**
     * @param $params
     * @param $value
     * @param $expected
     * @dataProvider filterData
     */
    public function testFilter($params, $value, $expected)
    {
        $filter = $this->getFilter($params);
        $this->assertEquals($expected, $filter->filtered($value));
    }

    /**
     * @param $params
     * @return Filter
     */
    protected function getFilter($params)
    {
        return new Filter($params);
    }

    /**
     * @return array
     */
    public function filterData()
    {
        return [

            [['name' => 'test'], '/some/file/name-test', true],
            [['name' => 'test'], '/some/file/test-name', true],
            [['name' => 'test'], '/some/file/test', true],
            [['name' => 'test'], '/some/file/te', false],
            [['name' => 'test'], '/some/file/name', false],
            [['name' => 'test'], '/some/test/name', false],

            [['extensions' => 'php'], '/some/test/name.php', true],
            [['extensions' => 'php'], '/some/test/name-php', false],
            [['extensions' => 'php'], '/some/php/name', false],
            [['extensions' => ['php', 'cpp']], '/some/test/name.cpp', true],
            [['extensions' => ['php', 'cpp']], '/some/test/name.php', true],
            [['extensions' => ['php', 'cpp']], '/some/php/name', false],
            [['extensions' => ['php', 'cpp']], '/some/cpp/name', false],
            [['extensions' => ['php', 'cpp']], '/some/folder/cppname', false],
            [['extensions' => ['php', 'cpp']], '/some/folder/phpname', false],
            [['extensions' => ['php', 'cpp']], '/some/folder/name.php.exe', false],
            [['extensions' => ['php', 'cpp']], '/some/folder/name.cpp.exe', false],

        ];
    }
}
