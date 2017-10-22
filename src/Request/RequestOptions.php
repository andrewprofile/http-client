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

namespace HttpClient\Request;

use HttpClient\Headers;
use HttpClient\Uri\Uri;
use HttpClient\Util\RequestUtil;

class RequestOptions
{
    const AUTH = 'auth';
    const BASE_URI = 'base_uri';
    const BODY = 'body';
    const HEADERS = 'headers';
    const JSON_BODY = 'is_json';

    /**
     * @var array
     */
    private $auth;
    
    /**
     * @var Uri|null
     */
    private $baseUri;
    
    /**
     * @var array
     */
    private $body;
    
    /**
     * @var Headers
     */
    private $headers;
    
    /**
     * @var bool
     */
    private $isJson;
    
    /**
     * RequestOptions constructor.
     * @param array $requestOptions
     */
    public function __construct(array $requestOptions)
    {
        $this->setRequestOption(self::AUTH, $requestOptions);
        $this->setRequestOption(self::BASE_URI, $requestOptions);
        $this->setRequestOption(self::BODY, $requestOptions);
        $this->setRequestOption(self::HEADERS, $requestOptions);
        $this->setRequestOption(self::JSON_BODY, $requestOptions);
    }
    
    /**
     * @return array|null
     */
    public function getAuth(): ?array
    {
        return $this->auth;
    }
    
    /**
     * @return Uri|null
     */
    public function getBaseUri(): ?Uri
    {
        return $this->baseUri;
    }
    
    /**
     * @return array|null
     */
    public function getBody(): ?array
    {
        return $this->body;
    }
    
    /**
     * @return Headers|null
     */
    public function getHeaders(): ?Headers
    {
        return $this->headers;
    }
    
    /**
     * @return bool
     */
    public function hasBody(): bool
    {
        return (bool) ($this->body|null);
    }
    
    /**
     * @return bool
     */
    public function isJson(): bool
    {
        return $this->isJson;
    }
    
    /**
     * @return bool
     */
    public function isWithCredentials(): bool
    {
        return (bool) ($this->auth|null);
    }
    
    /**
     * @param string $requestOption
     * @param array  $requestOptions
     * @return bool
     */
    protected function hasRequestOption(string $requestOption, array $requestOptions): bool
    {
        return isset($requestOptions[$requestOption]);
    }
    
    /**
     * @param string $requestOption
     * @param array  $requestOptions
     */
    protected function setRequestOption(string $requestOption, array $requestOptions): void
    {
        if ($this->hasRequestOption($requestOption, $requestOptions)) {
            $this->{RequestUtil::camelize($requestOption)} = $requestOptions[$requestOption];
            
            return;
        }
    
        $this->{RequestUtil::camelize($requestOption)} = null;
    }
}
