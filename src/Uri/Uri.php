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

namespace HttpClient\Uri;

use Psr\Http\Message\UriInterface;

final class Uri implements UriInterface
{
     /**
     * @var string
     */
    private $scheme = '';
    
    /**
     * @var string
     */
    private $userInfo = '';
    
    /**
     * @var string
     */
    private $host = '';
    
    /**
     * @var int|null
     */
    private $port;
    
    /**
     * @var string
     */
    private $path = '';
    
    /**
     * @var string
     */
    private $query = '';
    
    /**
     * @var string
     */
    private $fragment = '';
    
    /**
     * @var string
     */
    private $uriString = '';
    
    /**
     * Uri constructor.
     * @param string $uri
     * @throws \InvalidArgumentException
     */
    public function __construct(string $uri = '')
    {
        if (!empty($uri)) {
            $this->parseUri($uri);
        }
    }
    
    /**
     * @param string $uri
     * @throws \InvalidArgumentException
     */
    private function parseUri(string $uri): void
    {
        $parts = parse_url($uri);
        if ($parts === false) {
            throw new \InvalidArgumentException(
                'The source URI string appears to be malformed'
            );
        }
        $this->scheme = isset($parts['scheme']) ?  UriFilter::filterScheme($parts['scheme']) : '';
        $this->userInfo = $parts['user'] ?? '';
        $this->host = $parts['host'] ?? '';
        $this->port = $parts['port'] ?? null;
        $this->path = isset($parts['path']) ? UriFilter::filterPath($parts['path']) : '';
        $this->query = isset($parts['query']) ? UriFilter::filterQuery($parts['query']) : '';
        $this->fragment = isset($parts['fragment']) ? UriFilter::filterFragment($parts['fragment']) : '';
        
        if (isset($parts['pass'])) {
            $this->userInfo .= ':' . $parts['pass'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getHost(): string
    {
        if ($this->scheme === 'file' && !$this->host) {
            return 'localhost';
        }
        
        return $this->host;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getPort(): ?int
    {
        return UriFilter::isNonStandardPort($this->scheme, $this->host, $this->port)
            ? $this->port
            : null;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getQuery(): string
    {
        return $this->query;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withScheme($scheme): self
    {
        UriFilter::assertSchemeType($scheme);
        
        $scheme = UriFilter::filterScheme($scheme);
        if ($scheme === $this->scheme) {
            return clone $this;
        }
        
        $self = clone $this;
        $self->scheme = $scheme;
        
        return $self;
    }
    
    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function withUserInfo($user, $password = null): self
    {
        UriFilter::assertUserInfoType($user);
        
        $userInfo = $user;
        if ($password) {
            $userInfo .= ':' . $password;
        }
        
        if ($userInfo === $this->userInfo) {
            return clone $this;
        }
        
        $self = clone $this;
        $self->userInfo = $userInfo;
        
        return $self;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withHost($host): self
    {
        UriFilter::assertHostType($host);
        
        if ($host === $this->host) {
            return clone $this;
        }
        
        $self = clone $this;
        $self->host = $host;
        
        return $self;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withPort($port): self
    {
        UriFilter::assertPortValue($port);
        
        if ($port === $this->port) {
            return clone $this;
        }
        
        $self = clone $this;
        $self->port = $port;
        
        return $self;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withPath($path): self
    {
        UriFilter::assertPathValue($path);
        
        $path = UriFilter::filterPath($path);
        if ($path === $this->path) {
            return clone $this;
        }
        
        $self = clone $this;
        $self->path = $path;
        
        return $self;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withQuery($query): self
    {
        UriFilter::assertQueryValue($query);
        
        $query = UriFilter::filterQuery($query);
        if ($query === $this->query) {
            return clone $this;
        }
        
        $self = clone $this;
        $self->query = $query;
        
        return $self;
    }
    
    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function withFragment($fragment): self
    {
        UriFilter::assertFragmentType($fragment);
        
        $fragment = UriFilter::filterFragment($fragment);
        if ($fragment === $this->fragment) {
            return clone $this;
        }
        
        $self = clone $this;
        $self->fragment = $fragment;
        
        return $self;
    }
    
    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        if ($this->uriString !== '') {
            return $this->uriString;
        }
        
        $this->uriString = UriBuilder::createUriString(
            $this->scheme,
            $this->getAuthority(),
            $this->getPath(),
            UriBuilder::createQueryWithFragment($this->query, $this->fragment)
        );
        
        return $this->uriString;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAuthority(): string
    {
        if (empty($this->host)) {
            return '';
        }
        
        $authority = $this->host;
        if (!empty($this->userInfo)) {
            $authority = $this->userInfo . '@' . $authority;
        }
        
        if (UriFilter::isNonStandardPort($this->scheme, $this->host, $this->port)) {
            $authority .= ':' . $this->port;
        }
        
        return $authority;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    public function __clone()
    {
        $this->uriString = '';
    }
}
