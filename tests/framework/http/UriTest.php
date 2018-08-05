<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\framework\http;

use yii\http\Uri;
use yii\tests\TestCase;

class UriTest extends TestCase
{
    public function testSetupString()
    {
        $uri = Uri::fromString('http://example.com?foo=some');
        $this->assertEquals('http://example.com?foo=some', $uri->getString());
    }

    /**
     * @depends testSetupString
     */
    public function testParseString()
    {
        $uri = Uri::fromString('http://username:password@example.com:9090/content/path?foo=some#anchor');

        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('username:password', $uri->getUserInfo());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(9090, $uri->getPort());
        $this->assertSame('/content/path', $uri->getPath());
        $this->assertSame('foo=some', $uri->getQuery());
        $this->assertSame('anchor', $uri->getFragment());
    }

    /**
     * @depends testSetupString
     */
    public function testConstructFromString()
    {
        $uri = Uri::fromString('http://example.com?foo=some');
        $this->assertSame('http://example.com?foo=some', $uri->getString());
    }

    public function testConstructFromComponents()
    {
        $uri = (new Uri())
            ->withScheme('http')
            ->withUserInfo('username', 'password')
            ->withHost('example.com')
            ->withPort(9090)
            ->withPath('/content/path')
            ->withQuery('foo=some')
            ->withFragment('anchor');
        $this->assertSame('http://username:password@example.com:9090/content/path?foo=some#anchor', $uri->getString());
    }

    /**
     * @depends testConstructFromComponents
     */
    public function testToString()
    {
        $uri = (new Uri())
            ->withScheme('http')
            ->withHost('example.com')
            ->withPath('/content/path')
            ->withQuery('foo=some');

        $this->assertSame('http://example.com/content/path?foo=some', (string)$uri);
    }

    /**
     * @depends testParseString
     */
    public function testGetUserInfo()
    {
        $uri = Uri::fromString('http://username:password@example.com/content/path?foo=some');
        $this->assertSame('username:password', $uri->getUserInfo());
    }

    /**
     * @depends testParseString
     */
    public function testGetAuthority()
    {
        $uri = Uri::fromString('http://username:password@example.com/content/path?foo=some');
        $this->assertSame('username:password@example.com', $uri->getAuthority());
    }

    /**
     * @depends testConstructFromComponents
     */
    public function testOmitDefaultPort()
    {
        $uri = (new Uri())
            ->withScheme('http')
            ->withHost('example.com')
            ->withPort(80)
            ->withPath('/content/path')
            ->withQuery('foo=some');
        $this->assertSame('http://example.com/content/path?foo=some', $uri->getString());
    }

    /**
     * @depends testToString
     */
    public function testPsrSyntax()
    {
        $uri = (new Uri())
            ->withScheme('http')
            ->withUserInfo('username', 'password')
            ->withHost('example.com')
            ->withPort(9090)
            ->withPath('/content/path')
            ->withQuery('foo=some')
            ->withFragment('anchor');

        $this->assertSame('http://username:password@example.com:9090/content/path?foo=some#anchor', $uri->getString());
    }

    /**
     * @depends testConstructFromString
     * @depends testPsrSyntax
     */
    public function testModify()
    {
        $uri = Uri::fromString('http://example.com?foo=some')
            ->withHost('another.com')
            ->withPort(9090);

        $this->assertSame('http://another.com:9090?foo=some', $uri->getString());
    }

    /**
     * @depends testPsrSyntax
     */
    public function testImmutability()
    {
        $uri = (new Uri())
            ->withScheme('http')
            ->withUserInfo('username', 'password')
            ->withHost('example.com')
            ->withPort(9090)
            ->withPath('/content/path')
            ->withQuery('foo=some')
            ->withFragment('anchor');

        $this->assertSame($uri, $uri->withScheme('http'));
        $this->assertNotSame($uri, $uri->withScheme('https'));

        $this->assertSame($uri, $uri->withHost('example.com'));
        $this->assertNotSame($uri, $uri->withHost('another.com'));

        $this->assertSame($uri, $uri->withPort(9090));
        $this->assertNotSame($uri, $uri->withPort(33));

        $this->assertSame($uri, $uri->withPath('/content/path'));
        $this->assertNotSame($uri, $uri->withPath('/another/path'));

        $this->assertSame($uri, $uri->withQuery('foo=some'));
        $this->assertNotSame($uri, $uri->withQuery('foo=another'));

        $this->assertSame($uri, $uri->withFragment('anchor'));
        $this->assertNotSame($uri, $uri->withFragment('another'));

        $this->assertSame($uri, $uri->withUserInfo('username', 'password'));
        $this->assertNotSame($uri, $uri->withUserInfo('username', 'another'));
    }
}
