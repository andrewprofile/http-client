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

namespace HttpClient\Provider;

use HttpClient\Exception\InvalidArgumentException;
use HttpClient\Provider\Curl\CurlAuthMethod;
use HttpClient\Request\RequestOptions;
use HttpClient\Response\TextResponse;
use HttpClient\Uri\Uri;
use HttpClient\Uri\UriBuilder;
use HttpClient\Util\RequestUtil;
use Psr\Http\Message\ResponseInterface;

final class DummyProvider extends AbstractProvider
{
    const USERNAME = 0;
    const PASSWORD = 1;
    const AUTH_METHOD = 2;
    
    /**
     * @var Uri
     */
    private $uri;
    
    /**
     * @var array
     */
    private $headers;
    
    /**
     * @var array|string|null
     */
    private $body;
    
    /**
     * @var int
     */
    private $authMethod;
    
    /**
     * @var string
     */
    private $authData;
    
    /**
     * @param string         $method
     * @param Uri            $uri
     * @param RequestOptions $options
     * @return ResponseInterface
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws InvalidArgumentException
     */
    public function request(string $method, Uri $uri, RequestOptions $options): ResponseInterface
    {
        $this->uri = (new UriBuilder($options->getBaseUri(), $uri))->build();
        $this->headers = $options->getHeaders() ? $options->getHeaders()->map() : [];

        if ($options->isWithCredentials()) {
            $auth = $options->getAuth();
            $this->authMethod = $auth[self::AUTH_METHOD] ?? CurlAuthMethod::BASIC;
            $this->authData = "{$auth[self::USERNAME]}:{$auth[self::PASSWORD]}";
        }
        
        if ($options->hasBody()) {
            $this->body = $options->isJson() ? RequestUtil::jsonEncode($options->getBody()) : $options->getBody();
        }
        
        return new TextResponse('', 200, ['Content-Type' => 'application/json; charset=utf8']);
    }
    
    /**
     * @return Uri
     */
    public function getUri(): Uri
    {
        return $this->uri;
    }
    
    /**
     * @return array|string|null
     */
    public function getBody()
    {
        return $this->body;
    }
    
    /**
     * @return int
     */
    public function getAuthMethod(): int
    {
        return $this->authMethod;
    }
    
    /**
     * @return string
     */
    public function getAuthData(): string
    {
        return $this->authData;
    }
    
    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
