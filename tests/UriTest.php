<?php
/**
 * This file is part of the HttpClient package.
 *
 * (c) Andrzej Kostrzewa <andkos11@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace HttpClient;

use HttpClient\Uri\Uri;
use PHPUnit\Framework\TestCase;

final class UriTest extends TestCase
{
    public function testParsesUriAndRetrievePartsIndividually(): void
    {
        $uri = new Uri('https://username:password@example.com:8080/path/123?q=abc#test');
        
        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('username:password@example.com:8080', $uri->getAuthority());
        $this->assertSame('username:password', $uri->getUserInfo());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('/path/123', $uri->getPath());
        $this->assertSame('q=abc', $uri->getQuery());
        $this->assertSame('test', $uri->getFragment());
        $this->assertSame('https://username:password@example.com:8080/path/123?q=abc#test', (string) $uri);
    }
    
    public function testCanTransformAndRetrievePartsIndividually(): void
    {
        $uri = (new Uri())
            ->withScheme('https')
            ->withUserInfo('username', 'password')
            ->withHost('example.com')
            ->withPort(8080)
            ->withPath('/path/123')
            ->withQuery('q=abc')
            ->withFragment('test');
        
        $this->assertSame('https', $uri->getScheme());
        $this->assertSame('username:password@example.com:8080', $uri->getAuthority());
        $this->assertSame('username:password', $uri->getUserInfo());
        $this->assertSame('example.com', $uri->getHost());
        $this->assertSame(8080, $uri->getPort());
        $this->assertSame('/path/123', $uri->getPath());
        $this->assertSame('q=abc', $uri->getQuery());
        $this->assertSame('test', $uri->getFragment());
        $this->assertSame('https://username:password@example.com:8080/path/123?q=abc#test', (string) $uri);
    }
    
    public function testImmutability(): void
    {
        $uri = new Uri();

        $this->assertNotSame($uri, $uri->withScheme('https'));
        $this->assertNotSame($uri, $uri->withUserInfo('username', 'password'));
        $this->assertNotSame($uri, $uri->withHost('example.com'));
        $this->assertNotSame($uri, $uri->withPort(8080));
        $this->assertNotSame($uri, $uri->withPath('/path/123'));
        $this->assertNotSame($uri, $uri->withQuery('q=abc'));
        $this->assertNotSame($uri, $uri->withFragment('test'));
    }
    
    public function testDefaultReturnValuesOfGetters(): void
    {
        $uri = new Uri();

        $this->assertSame('', $uri->getScheme());
        $this->assertSame('', $uri->getAuthority());
        $this->assertSame('', $uri->getUserInfo());
        $this->assertSame('', $uri->getHost());
        $this->assertNull($uri->getPort());
        $this->assertSame('', $uri->getPath());
        $this->assertSame('', $uri->getQuery());
        $this->assertSame('', $uri->getFragment());
        $this->assertSame('', (string) $uri);
    }
}
