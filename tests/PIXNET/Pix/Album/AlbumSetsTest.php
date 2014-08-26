<?php
class Pix_Album_SetsTest extends PHPUnit_Framework_TestCase
{
    public static $pixapi;

    public static function setUpBeforeClass()
    {
        Authentication::setUpBeforeClass();
        self::$pixapi = Authentication::$pixapi;
    }

    /**
     * 產生測試用的相簿
     */
    private function createTempSets()
    {
        for ($i = 0; $i < 5; $i++) {
            $title = "PHP-SDK-TEST-TITLE-" . sha1($i);
            $desc = "PHP-SDK-TEST-DESC-" . md5($i);
            $expected[] = self::$pixapi->album->sets->create($title, $desc)['data'];
        }
        return $expected;
    }

    /**
     * 刪除測試用的相簿
     */
    private function destroyTempSets($sets)
    {
        foreach ($sets as $set) {
            self::$pixapi->album->sets->delete($set['id']);
        }
    }

    public static function tearDownAfterClass()
    {
        Authentication::tearDownAfterClass();
    }

    /**
     * @expectedException PixAPIException
     */
    public function testGet()
    {
        self::$pixapi->album->sets->test->test();
    }

    /**
     * @expectedException PixAPIException
     */
    public function testCreateException()
    {
        self::$pixapi->album->sets->create('', '');
    }

    public function testCreate()
    {
        for ($i = 0; $i < 5; $i++) {
            $title = "PHP-SDK-TEST-TITLE-" . sha1($i);
            $desc = "PHP-SDK-TEST-DESC-" . md5($i);
            $ret = self::$pixapi->album->sets->create($title, $desc)['data'];
            $this->assertEquals($title, $ret['title']);
            $this->assertEquals($desc, $ret['description']);
            self::$pixapi->album->sets->delete($ret['id']);
        }
    }

    /**
     * @expectedException PixAPIException
     */
    public function testPositionException()
    {
        self::$pixapi->Album->sets->position('', '');
    }

    public function testPosition()
    {
        $current_albumsets = self::$pixapi->Album->sets->search('emmatest', ['parent_id' => 4948779])['data'];
        $num_of_sets = count($current_albumsets);
        $i = 1;
        foreach ($current_albumsets as $set) {
            $new_order[$i++ % $num_of_sets] = $set['id'];
        }
        ksort($new_order);
        $expected = $new_order;
        $ret_albumsets = self::$pixapi->Album->sets->position('4948779', implode(',', $new_order))['data'];
        foreach ($ret_albumsets as $set) {
            $actual[] = $set['id'];
        }
        $this->assertEquals($actual, $expected);
    }

    /**
     * @expectedException PixAPIException
     */
    public function testSearchException()
    {
        self::$pixapi->Album->Sets->search('');
    }

    /**
     * @expectedException PixAPIException
     */
    public function testSearchTwoParamsException()
    {
        self::$pixapi->Album->Sets->search('', '');
    }

    public function testSearch()
    {

        $tempSets = $this->createTempSets();
        foreach ($tempSets as $set) {
            $expected['title'][] = $set['title'];
            $expected['id'][] = $set['id'];
        }
        $current = self::$pixapi->Album->Sets->search('emmatest')['data'];
        foreach ($current as $set) {
            $actual[] = $set['title'];
        }
        foreach ($expected['title'] as $set) {
            $this->assertTrue(in_array($set, $actual));
        }

        foreach ($expected['id'] as $set_id) {
            $ret = self::$pixapi->album->sets->search('emmatest', ['set_id' => $set_id])['data'];
            $this->assertEquals($set_id, $ret['id']);
        }

        $this->destroyTempSets($tempSets);
    }

    /**
     * @expectedException PixAPIException
     */
    public function testElementsException()
    {
        self::$pixapi->album->sets->elements('', '');
    }

    public function testElements()
    {
        // 以此相簿為測試範本 http://emmatest.pixnet.net/album/set/4948710
        $expected = ['167691000', '167691006'];
        $current_elements = self::$pixapi->album->sets->elements('emmatest', 4948710)['data'];
        foreach ($current_elements as $ele) {
            $actual[] = $ele['id'];
        }
        $this->assertEquals($actual, $expected);
    }

    /**
     * @expectedException PixAPIException
     */
    public function testCommentsException()
    {
        self::$pixapi->album->sets->comments('', '');
    }

    public function testComments()
    {
        // 以此相簿為測試範本 http://emmatest.pixnet.net/album/set/4948710
        $comment = self::$pixapi->Album->comments->create('emmatest', 4948710, 'test message')['data'];
        $current_albumcomments = self::$pixapi->album->sets->comments('emmatest', 4948710)['data'][0];
        $this->assertEquals($current_albumcomments['id'], $comment['id']);
        self::$pixapi->Album->comments->delete($current_albumcomments['id']);
    }

    /**
     * @expectedException PixAPIException
     */
    public function testUpdateException()
    {
        self::$pixapi->album->sets->update('', '', '');
    }

    public function testUpdate()
    {
        $expected_title = "unit test title";
        $expected_desc = "unit test description";
        $current_set = self::$pixapi->album->sets->search('emmatest', ['set_id' => 4948710])['data'];
        $current_title = $current_set['title'];
        $current_desc = $current_set['description'];

        $actualset = self::$pixapi->album->sets->update(4948710, $expected_title, $expected_desc)['data'];
        $this->assertEquals($actualset['title'], $expected_title);
        $this->assertEquals($actualset['description'], $expected_desc);
        self::$pixapi->album->sets->update(4948710, $current_title, $current_desc, ['parent_id' => '4948779']);
    }

    /**
     * @expectedException PixAPIException
     */
    public function testDeleteException()
    {
        self::$pixapi->album->sets->delete('');
    }

    public function testDelete()
    {
        $tempSets = $this->createTempSets();
        $expected = count($tempSets);
        $actual = 0;
        foreach ($tempSets as $set) {
            $ret = self::$pixapi->album->sets->delete($set['id']);
            if (!$ret['error']) {
                $actual++;
            }
        }
        $this->assertEquals($actual, $expected);
    }

    /**
     * @expectedException PixAPIException
     */
    public function testNearbyException()
    {
        $ret = self::$pixapi->album->sets->nearby('', '', '');
    }

    public function testNearby()
    {
        $expected = ['34260'];
        $options = array('distance_max' => 3500);
        $ret = self::$pixapi->album->sets->nearby('emmademo', '25.058172', '121.535304', $options)['data'];
        foreach ($ret as $set) {
            $this->assertTrue(in_array($set['id'], $expected));
        }
    }
}
