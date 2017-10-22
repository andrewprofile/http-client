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

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    /**
     * @var null|resource
     */
    protected $resource;
    
    /**
     * @var string|resource
     */
    private $stream;
    
    /**
     * Stream constructor.
     * @param        $stream
     * @param string $mode
     * @throws \InvalidArgumentException
     */
    public function __construct($stream, $mode = 'r')
    {
        $this->setStream($stream, $mode);
    }
    
    public function __destruct()
    {
        $this->close();
    }
    
    /**
     * @param        $stream
     * @param string $mode
     * @throws \InvalidArgumentException
     */
    private function setStream($stream, $mode = 'r')
    {
        $error = null;
        $resource = $stream;
        if (is_string($stream)) {
            set_error_handler(function ($streamError) use (&$error) {
                $error = $streamError;
            }, E_WARNING);
            $resource = fopen($stream, $mode);
            restore_error_handler();
        }
        
        if ($error) {
            throw new \InvalidArgumentException('Invalid stream reference provided');
        }
        
        if (!is_resource($resource) || 'stream' !== get_resource_type($resource)) {
            throw new \InvalidArgumentException(
                'Invalid stream provided; must be a string stream identifier or stream resource'
            );
        }
        
        if ($stream !== $resource) {
            $this->stream = $stream;
        }
        
        $this->resource = $resource;
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        if (!$this->isReadable()) {
            return '';
        }
        
        try {
            $this->rewind();
            return $this->getContents();
        } catch (\RuntimeException $e) {
            return '';
        }
    }
    
    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        if (!$this->resource) {
            return false;
        }
        
        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];
        
        return (false !== strpos($mode, 'r') || false !== strpos($mode, '+'));
    }
    
    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->seek(0);
    }
    
    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET): bool
    {
        if (!$this->resource) {
            throw new \RuntimeException('No resource available; cannot seek position');
        }
        
        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        }
        
        $result = fseek($this->resource, $offset, $whence);
        if ($result !== 0) {
            throw new \RuntimeException('Error seeking within stream');
        }
        
        return true;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        if (!$this->resource) {
            return false;
        }
        
        $meta = stream_get_meta_data($this->resource);
        
        return $meta['seekable'];
    }
    
    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        if (!$this->isReadable()) {
            throw new \RuntimeException('Stream is not readable');
        }
        
        $result = stream_get_contents($this->resource);
        if ($result === false) {
            throw new \RuntimeException('Error reading from stream');
        }
        
        return $result;
    }
    
    /**
     * @param        $resource
     * @param string $mode
     * @throws \InvalidArgumentException
     */
    public function attach($resource, $mode = 'r'): void
    {
        $this->setStream($resource, $mode);
    }
    
    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        if (!$this->resource) {
            return;
        }
        
        $resource = $this->detach();
        fclose($resource);
    }
    
    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        
        return $resource;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        if ($this->resource === null) {
            return null;
        }
        
        $stats = fstat($this->resource);
        
        return $stats['size'];
    }
    
    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        if (!$this->resource) {
            throw new \RuntimeException('No resource available; cannot tell position');
        }
        
        $result = ftell($this->resource);
        if (!is_int($result)) {
            throw new \RuntimeException('Error occurred during tell operation');
        }
        
        return $result;
    }
    
    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        if (!$this->resource) {
            return true;
        }
        
        return feof($this->resource);
    }
    
    /**
     * {@inheritdoc}
     */
    public function write($string): int
    {
        if (!$this->resource) {
            throw new \RuntimeException('No resource available; cannot write');
        }
        
        if (!$this->isWritable()) {
            throw new \RuntimeException('Stream is not writable');
        }
        
        $result = fwrite($this->resource, $string);
        if ($result === false) {
            throw new \RuntimeException('Error writing to stream');
        }
        
        return $result;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        if (!$this->resource) {
            return false;
        }
        
        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];
        
        return (
            false !== strpos($mode, 'x')
            || false !== strpos($mode, 'w')
            || false !== strpos($mode, 'c')
            || false !== strpos($mode, 'a')
            || false !== strpos($mode, '+')
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        if (!$this->resource) {
            throw new \RuntimeException('No resource available; cannot read');
        }
        
        if (!$this->isReadable()) {
            throw new \RuntimeException('Stream is not readable');
        }
        
        $result = fread($this->resource, $length);
        if ($result === false) {
            throw new \RuntimeException('Error reading stream');
        }
        
        return $result;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if ($key === null) {
            return stream_get_meta_data($this->resource);
        }
        
        $metadata = stream_get_meta_data($this->resource);
        if (!array_key_exists($key, $metadata)) {
            return null;
        }
        
        return $metadata[$key];
    }
}
