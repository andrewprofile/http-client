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

final class HeadersTest extends TestCase
{
    public function testSetHeaders(): void
    {
        $headersCollection = ['bar' => 'baz'];
        $headers = new Headers(new Uri('http://foo.com'), $headersCollection);
    
        $this->assertEquals(
            ['bar' => ['baz']],
            $headers->getHeaders()
        );
    
        $headersCollection = ['bar' => 'baz'];
        $newHeadersCollection = ['foo' => 'bar'];
        $headers = new Headers(new Uri('http://foo.com'), $headersCollection);
        
        $headers->setHeaders($newHeadersCollection);
    
        $this->assertEquals(
            ['foo' => ['bar']],
            $headers->getHeaders()
        );
    }
    
    public function testMapHeaders(): void
    {
        $headersCollection = ['bar' => 'baz'];
        $headers = new Headers(new Uri('http://foo.com'), $headersCollection);
    
        $this->assertEquals(
            ['bar: baz'],
            $headers->map()
        );
    }
    
    public function testSetHeader(): void
    {
        $headers = new Headers(new Uri('http://foo.com'), []);
    
        $headers->setHeader('bar', 'baz');
        $this->assertEquals(
            ['baz'],
            $headers->getHeader('bar')
        );
    }
    
    public function testRemoveHeader(): void
    {
        $headersCollection = [
            'bar' => 'baz',
            'foo' => 'bar',
        ];
        $headers = new Headers(new Uri('http://foo.com'), $headersCollection);
        
        $headers->removeHeader('bar');
    
        $this->assertEquals(
            ['foo' => ['bar']],
            $headers->getHeaders()
        );
    }
    
    public function testSetHost(): void
    {
        $headers = new Headers(new Uri('http://foo.com'), []);
        
        $headers->setHost('bar.com');

        $this->assertEquals(
            ['bar.com'],
            $headers->getHeader('Host')
        );
    }
}
