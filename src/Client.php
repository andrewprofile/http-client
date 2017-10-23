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

use HttpClient\Provider\Provider;
use HttpClient\Request\RequestOptions;
use HttpClient\Uri\Uri;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /**
     * @var Provider
     */
    protected $provider;
    
    /**
     * Client constructor.
     * @param Provider $provider
     */
    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }
    
    /**
     * @param Provider $provider
     * @return Client
     */
    public function withProvider(Provider $provider): Client
    {
        $self = clone $this;
        $self->provider = $provider;
        return $self;
    }
    
    /**
     * @param Uri            $uri
     * @param RequestOptions $options
     * @return ResponseInterface
     */
    public function get(Uri $uri, RequestOptions $options): ResponseInterface
    {
        return $this->provider->get($uri, $options);
    }
    
    /**
     * @param Uri            $uri
     * @param RequestOptions $options
     * @return ResponseInterface
     */
    public function post(Uri $uri, RequestOptions $options): ResponseInterface
    {
        return $this->provider->post($uri, $options);
    }
    
    /**
     * @param string         $method
     * @param Uri            $uri
     * @param RequestOptions $options
     * @return ResponseInterface
     */
    public function request(string $method, Uri $uri, RequestOptions $options): ResponseInterface
    {
        return $this->provider->request($method, $uri, $options);
    }
}
