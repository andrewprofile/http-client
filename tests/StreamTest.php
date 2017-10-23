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

use PHPUnit\Framework\TestCase;

final class StreamTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorThrowsExceptionOnInvalidArgument(): void
    {
        new Stream(true);
    }
    
    public function testConstructorInitializesProperties(): void
    {
        $handle = fopen('php://temp', 'rb+');
        fwrite($handle, 'data');
        $stream = new Stream($handle);
        
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
        $this->assertEquals('php://temp', $stream->getMetadata('uri'));
        $this->assertInternalType('array', $stream->getMetadata());
        $this->assertEquals(4, $stream->getSize());
        $this->assertFalse($stream->eof());
        
        $stream->close();
    }
    
    public function testStreamClosesHandleOnDestruct(): void
    {
        $handle = fopen('php://temp', 'rb');
        $stream = new Stream($handle);
        unset($stream);
        
        $this->assertFalse(is_resource($handle));
    }
    
    public function testConvertsToString(): void
    {
        $handle = fopen('php://temp', 'wb+');
        fwrite($handle, 'data');
        $stream = new Stream($handle);
        
        $this->assertEquals('data', (string) $stream);
        $this->assertEquals('data', (string) $stream);
        
        $stream->close();
    }
    
    public function testGetsContents(): void
    {
        $handle = fopen('php://temp', 'wb+');
        fwrite($handle, 'data');
        $stream = new Stream($handle);
        
        $this->assertEquals('', $stream->getContents());
        $stream->seek(0);
        $this->assertEquals('data', $stream->getContents());
        $this->assertEquals('', $stream->getContents());
        
        $stream->close();
    }
}
