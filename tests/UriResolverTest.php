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
use HttpClient\Uri\UriResolver;
use PHPUnit\Framework\TestCase;

final class UriResolverTest extends TestCase
{
    public function testResolveUri(): void
    {
        $uri = new Uri('https://username:password@example.com:8080/path/123?q=abc#test');
        $baseUri = new Uri('https://username:password@example.com:8080/');
        $uriWithBaseUri = $uri;
        if ($baseUri instanceof Uri) {
            $uriResolver = new UriResolver();
            $uriWithBaseUri = $uriResolver($baseUri, $uri);
        }
    
        $this->assertSame('https', $uriWithBaseUri->getScheme());
        $this->assertSame('username:password@example.com:8080', $uriWithBaseUri->getAuthority());
        $this->assertSame('username:password', $uriWithBaseUri->getUserInfo());
        $this->assertSame('example.com', $uriWithBaseUri->getHost());
        $this->assertSame(8080, $uriWithBaseUri->getPort());
        $this->assertSame('/path/123', $uriWithBaseUri->getPath());
        $this->assertSame('q=abc', $uriWithBaseUri->getQuery());
        $this->assertSame('test', $uriWithBaseUri->getFragment());
    }
}
