<?php

namespace Bangpound\Assh\Tests\Twig;

use Bangpound\Assh\Twig\Extension;
use PHPUnit\Framework\TestCase;

class ExtensionTest extends TestCase
{
    /**
     * @dataProvider arrayFilterProvider
     *
     * @param array $input
     * @param array $expected
     */
    public function testArrayFilter(array $input, array $expected)
    {
        $extension = new Extension();

        $actual = $extension->arrayFilter($input);

        $this->assertSame($expected, $actual);
    }

    public function arrayFilterProvider()
    {
        yield [
            [true, true, false, true],
            [true, true, true],
        ];

        yield [
            ['something', 0, 'something else'],
            ['something', 'something else'],
        ];
    }

    /**
     * @dataProvider propertySortProvider
     *
     * @param \Traversable|array  $input
     * @param string $property
     * @param array  $expected
     *
     * @throws \Twig_Error_Runtime
     */
    public function testPropertySort($input, string $property, array $expected)
    {
        $extension = new Extension();

        $actual = $extension->propertySort($input, $property);

        $this->assertSame($expected, $actual);
    }

    public function propertySortProvider()
    {
        yield [
            [
                ['index' => 'a'],
                ['index' => 'd'],
                ['index' => 'f'],
                ['index' => 'b'],
                ['index' => 'c'],
                ['index' => 'e'],
            ],
            'index',
            [
                ['index' => 'a'],
                ['index' => 'b'],
                ['index' => 'c'],
                ['index' => 'd'],
                ['index' => 'e'],
                ['index' => 'f'],
            ]
        ];

        yield [
            [
                ['index' => 'a'],
                ['index' => 'D'],
                ['index' => 'f'],
                ['index' => 'B'],
                ['index' => 'c'],
                ['index' => 'E'],
            ],
            'index',
            [
                ['index' => 'a'],
                ['index' => 'B'],
                ['index' => 'c'],
                ['index' => 'D'],
                ['index' => 'E'],
                ['index' => 'f'],
            ]
        ];

        yield [
            new \ArrayObject([
                ['index' => 'a'],
                ['index' => 'd'],
                ['index' => 'f'],
                ['index' => 'b'],
                ['index' => 'c'],
                ['index' => 'e'],
            ]),
            'index',
            [
                ['index' => 'a'],
                ['index' => 'b'],
                ['index' => 'c'],
                ['index' => 'd'],
                ['index' => 'e'],
                ['index' => 'f'],
            ]
        ];
    }

    /**
     * @expectedException \Twig_Error_Runtime
     */
    public function testPropertySortThrowsExceptionsForNonArrays()
    {
        $extension = new Extension();

        $input = new \stdClass;
        $input->thing = 'a';

        $extension->propertySort($input, 'thing');
    }

    /**
     * @dataProvider jpProvider
     *
     * @param array  $input
     * @param string $expression
     * @param mixed  $expected
     */
    public function testJp(array $input, string $expression, $expected)
    {
        $extension = new Extension();

        $actual = $extension->jp($expression, $input);

        $this->assertSame($expected, $actual);
    }

    public function jpProvider()
    {
        yield [
            [
                'key' => 'value',
            ],
            'key',
            'value',
        ];
    }
}
