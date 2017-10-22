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

use HttpClient\Response\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

final class ResponseTest extends TestCase
{
    public function testDefaultConstructor(): void
    {
        $response = new Response();
        
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('1.1', $response->getProtocolVersion());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame([], $response->getHeaders());
        $this->assertInstanceOf(StreamInterface::class, $response->getBody());
        $this->assertSame('', (string) $response->getBody());
    }
    
    public function testCanConstructWithStatusCode(): void
    {
        $response = new Response('php://temp', 404);
        
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Not Found', $response->getReasonPhrase());
    }
    
    public function testCanConstructWithHeaders(): void
    {
        $response = new Response('php://temp', 200, ['Foo' => 'Bar']);
        
        $this->assertSame(['Foo' => ['Bar']], $response->getHeaders());
        $this->assertSame('Bar', $response->getHeaderLine('Foo'));
        $this->assertSame(['Bar'], $response->getHeader('Foo'));
    }
}
