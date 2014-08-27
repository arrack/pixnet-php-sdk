<?php
class Pix_Blog_CommentsTest extends PHPUnit_Framework_TestCase
{
    public static $pixapi;
    public static $test_comments;

    public static function setUpBeforeClass()
    {
        Authentication::setUpBeforeClass();
        self::$pixapi = Authentication::$pixapi;
        self::$test_comments = self::$pixapi->blog->comments->search(['article_id' => 11903807]);
    }

    private function createTempArticle()
    {
        return self::$pixapi->blog->articles->create('Emma unit test article', 'unit test body')['data'];
    }

    private function destroyTempArticle($article)
    {
        self::$pixapi->blog->articles->delete($article['id']);
    }

    /**
     * @expectedException PixAPIException
     * @expectedExceptionCode PixAPIException::CLASS_NOT_FOUND
     */
    public function testSubClassNotFoundException()
    {
        $actual = self::$pixapi->blog->comments->notfound;
    }

    /**
     * @expectedException PixAPIException
     */
    public function testSearchException()
    {
        self::$pixapi->blog->comments->search('TEST');
    }

    public function testSearchArticleComments()
    {
        $comments = self::$pixapi->blog->comments->search(['article_id' => 11903807]);
        $this->assertEquals(2, $comments['total']);
    }

    public function testSearchAll()
    {
        $comments = self::$pixapi->blog->comments->search();
        $this->assertEquals(35, $comments['total']);
    }

    public function testSearchComment()
    {
        $article = $this->createTempArticle();
        $body = "unit test" . md5(time);
        $comment = self::$pixapi->blog->comments->create('emmatest', $article['id'], $body)['data'];
        $expected = $comment['body'];
        $actual = self::$pixapi->blog->comments->search($comment['id'])['data'];
        $this->assertEquals($expected, $actual['body']);
        $this->destroyTempArticle($article);
    }

    public function testCreate()
    {
        $article = $this->createTempArticle();
        $body = "unit test";
        $comment = self::$pixapi->blog->comments->create('emmatest', $article['id'], $body)['data'];
        $expected = $comment['body'];
        $actual = self::$pixapi->blog->comments->search($comment['id'])['data'];
        $this->assertEquals($expected, $actual['body']);
        $this->destroyTempArticle($article);
    }

    public function testLatest()
    {
        $actual = self::$pixapi->blog->comments->latest();
        $this->assertLessThanOrEqual(5, $actual['total']);
    }
}
