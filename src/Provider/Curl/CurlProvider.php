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

namespace HttpClient\Provider\Curl;

use HttpClient\Exception\InvalidArgumentException;
use HttpClient\Headers;
use HttpClient\Provider\AbstractProvider;
use HttpClient\Provider\Exception\ProviderException;
use HttpClient\Provider\Provider;
use HttpClient\Provider\ProviderOption;
use HttpClient\Request\RequestOptions;
use HttpClient\Response\TextResponse;
use HttpClient\Uri\Uri;
use HttpClient\Uri\UriBuilder;
use HttpClient\Util\RequestUtil;
use Psr\Http\Message\ResponseInterface;

final class CurlProvider extends AbstractProvider implements Provider, ProviderOption
{
    const USERNAME = 0;
    const PASSWORD = 1;
    const AUTH_METHOD = 2;
    
    /**
     * @var resource
     */
    private $handle;
    
    /**
     * @var string
     */
    private $responseHeader = '';
    
    /**
     * CurlProvider constructor.
     * @throws ProviderException
     */
    public function __construct()
    {
        if (!self::isAvailable()) {
            throw ProviderException::notLoaded('Curl');
        }
        
        $this->handle = curl_init();
        $this->initOptions();
    }

    public function __destruct()
    {
        curl_close($this->handle);
    }
    
    public function __clone()
    {
        $this->handle = curl_copy_handle($this->handle);
    }
    
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
        $this->responseHeader = '';
        $uriWithBaseUri = (new UriBuilder($options->getBaseUri(), $uri))->build();
        $this->setOptions([
            CURLOPT_URL => (string) $uriWithBaseUri,
            CURLOPT_HTTPHEADER => $options->getHeaders() ? $options->getHeaders()->map() : [],
            CURLOPT_CUSTOMREQUEST => $method,
        ]);

        $this->setAuth($options);
        
        if ($options->hasBody()) {
            $this->setOption(CURLOPT_POSTFIELDS, function () use ($options) {
                if ($options->isJson()) {
                    return RequestUtil::jsonEncode($options->getBody());
                }
                
                return $options->getBody();
            });
        }

        $response = curl_exec($this->handle);
        $httpCode = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
    
        return new TextResponse(
            (string) $response,
            $httpCode ?: 500,
            (new Headers($uriWithBaseUri))->parse($this->responseHeader)
        );
    }
    
    /**
     * @param int      $option
     * @param callable $value
     * @return bool
     */
    public function setOption(int $option, callable $value): bool
    {
        return curl_setopt($this->handle, $option, $value());
    }
    
    /**
     * @param array $options
     * @return bool
     */
    public function setOptions(array $options): bool
    {
        return curl_setopt_array($this->handle, $options);
    }
    
    /**
     * @param resource $handle
     * @param string $headerLine
     * @return int
     */
    private function readHeader($handle, string $headerLine): int
    {
        $this->responseHeader .= $headerLine;
    
        return strlen($headerLine);
    }
    
    /**
     * @return void
     */
    private function initOptions(): void
    {
        $this->setOptions([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT       => 'HttpClient/' . self::VERSION,
            CURLOPT_HEADERFUNCTION => [$this,'readHeader'],
        ]);
    }
    
    /**
     * @return bool
     */
    private static function isAvailable(): bool
    {
        return extension_loaded('curl');
    }
    
    /**
     * @param RequestOptions $options
     */
    private function setAuth(RequestOptions $options): void
    {
        if ($options->isWithCredentials()) {
            $auth = $options->getAuth();
            $this->setOption(CURLOPT_HTTPAUTH, function () use ($auth) {
                return $auth[self::AUTH_METHOD] ?? CurlAuthMethod::BASIC;
            });
        
            $this->setOption(CURLOPT_USERPWD, function () use ($auth) {
                return "{$auth[self::USERNAME]}:{$auth[self::PASSWORD]}";
            });
        }
    }
}
