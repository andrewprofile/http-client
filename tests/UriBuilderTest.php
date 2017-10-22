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
use HttpClient\Uri\UriBuilder;
use PHPUnit\Framework\TestCase;

final class UriBuilderTest extends TestCase
{
    public function testBuildUri(): void
    {
        $uriBuilder = new UriBuilder(new Uri('http://foo.com'), new Uri('/bar'));
        $uriWithBaseUri = $uriBuilder->build();
    
        $this->assertEquals(
            'http://foo.com/bar',
            $uriWithBaseUri
        );
    }
    
    public function testBuildUriString(): void
    {
        $uri = new Uri('https://username:password@example.com:8080/path/123?q=abc#test');
        
        $uriBuildString = UriBuilder::createUriString(
            $uri->getScheme(),
            $uri->getAuthority(),
            $uri->getPath(),
            UriBuilder::createQueryWithFragment($uri->getQuery(), $uri->getFragment())
        );
    
        $this->assertSame('https://username:password@example.com:8080/path/123?q=abc#test', $uriBuildString);
    }
    
    public function testBuildQueryWithFragment(): void
    {
        $uri = new Uri('https://username:password@example.com:8080/path/123?q=abc#test');
        $queryWithFragment = UriBuilder::createQueryWithFragment($uri->getQuery(), $uri->getFragment());
    
        $this->assertSame('?q=abc#test', $queryWithFragment);
    }
}
