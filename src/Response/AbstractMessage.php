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

namespace HttpClient\Response;

use HttpClient\HeadersFilter;
use HttpClient\Headers;
use HttpClient\Util\HttpSecurity;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class AbstractMessage implements MessageInterface
{
    /**
     * @var Headers
     */
    protected $headers;
    
    /**
     * @var string
     */
    protected $protocolVersion = '1.1';
    
    /**
     * @var \Psr\Http\Message\StreamInterface
     */
    protected $stream;
    
    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }
    
    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function withProtocolVersion($version): self
    {
        HttpSecurity::assertValidProtocolVersion($version);
        $self = clone $this;
        $self->protocolVersion = $version;
        
        return $self;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getHeaders(): array
    {
        return $this->headers->getHeaders();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getHeaderLine($name): string
    {
        $headerLine = $this->getHeader($name);
        if (empty($headerLine)) {
            return '';
        }
        
        return implode(',', $headerLine);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getHeader($name): array
    {
        return $this->headers->getHeader($name);
    }
    
    /**
     * {@inheritdoc}
     */
    public function withAddedHeader($name, $value): self
    {
        [$name, $value] = HeadersFilter::assertValidHeader($name, $value);
        if (!$this->hasHeader($name)) {
            return $this->withHeader($name, $value);
        }
        
        $self = clone $this;
        $self->headers->addHeader($name, $value);
        
        return $self;
    }
    
    /**
     * {@inheritdoc}
     */
    public function hasHeader($name): bool
    {
        return $this->headers->hasHeader($name);
    }
    
    /**
     * {@inheritdoc}
     */
    public function withHeader($name, $value): self
    {
        [$name, $value] = HeadersFilter::assertValidHeader($name, $value);
        $self = clone $this;
        $self->headers->setHeader($name, $value);
        
        return $self;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withoutHeader($name): self
    {
        if (!$this->hasHeader($name)) {
            return clone $this;
        }
        
        $self = clone $this;
        $self->headers->removeHeader($name);
        
        return $self;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getBody(): StreamInterface
    {
        return $this->stream;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withBody(StreamInterface $body): self
    {
        $self = clone $this;
        $self->stream = $body;
        
        return $self;
    }
}
