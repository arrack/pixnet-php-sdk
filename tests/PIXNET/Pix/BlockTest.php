<?php
class Pix_Block_Test extends PHPUnit_Framework_TestCase
{
    public static $pixapi;

    public static function setUpBeforeClass()
    {
        Authentication::setUpBeforeClass();
        self::$pixapi = Authentication::$pixapi;
    }

    public static function tearDownAfterClass()
    {
        Authentication::tearDownAfterClass();
    }

    public function testCreate()
    {
        $current = self::$pixapi->block->search()['data'];
        if ("emma" == $current[0]['user']['name']) {
            self::$pixapi->block->delete('emma');
        }
        $actual_all = self::$pixapi->block->create('emma');
        $actual = $actual_all['data']['user']['name'];

        $this->assertEquals('emma', $actual);
    }

    /**
     * @expectedException PixAPIException
     */
    public function testCreateException()
    {
        $actual = self::$pixapi->block->create('');
    }

    /**
     * @expectedException PixAPIException
     */
    public function testGet()
    {
        self::$pixapi->block->test->test();
    }

    public function testSearch()
    {
        $actual_all = self::$pixapi->block->search()['data'];
        $actual = $actual_all[0]['user']['name'];
        $expected = 'emma';

        $this->assertEquals($expected, $actual);
    }

    public function testDelete()
    {
        $current = self::$pixapi->block->search();
        if (0 == $current['total']) {
            self::$pixapi->block->create('emma');
        }
        self::$pixapi->block->delete('emma');
        $actual = self::$pixapi->block->search()['data'];
        $this->assertNull($actual);
    }

    /**
     * @expectedException PixAPIException
     */
    public function testDeleteException()
    {
        $actual = self::$pixapi->block->delete('');
    }

    /**
     * @expectedException PixAPIException
     * @expectedExceptionCode PixAPIException::CLASS_NOT_FOUND
     */
    public function testSubClassNotFoundException()
    {
        $actual = self::$pixapi->block->notfound;
    }
}
