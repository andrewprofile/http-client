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

use HttpClient\Util\HttpSecurity;
use Psr\Http\Message\UriInterface;

final class Headers
{
    /**
     * @var array|mixed
     */
    private $headers;
    
    /**
     * @var UriInterface
     */
    private $uri;
    
    /**
     * @var array
     */
    private $headerNames = [];
    
    /**
     * Headers constructor.
     * @param array        $headers
     * @param UriInterface $uri
     * @throws \InvalidArgumentException
     */
    public function __construct(UriInterface $uri, array $headers = [])
    {
        [$this->headerNames, $headers] = self::filterHeaders($headers);
        $this->assertHeaders($headers);
        $this->headers = $headers;
        $this->uri = $uri;
    }
    
    /**
     * @param $name
     * @return string
     */
    public static function normalizeHeader($name): string
    {
        return strtolower($name);
    }
    
    /**
     * @param       $contentType
     * @param array $headers
     * @return array
     */
    public static function injectContentType($contentType, array $headers): array
    {
        $hasContentType = array_reduce(array_keys($headers), function ($carry, $item) {
            return $carry ?: (strtolower($item) === 'content-type');
        }, false);
        
        if (!$hasContentType) {
            $headers['content-type'] = [$contentType];
        }
        
        return $headers;
    }
    
    /**
     * @param $name
     * @param $value
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function assertValidHeader($name, $value): array
    {
        if (is_string($value)) {
            $value = [$value];
        }
        
        if (!is_array($value) || !self::arrayContainsOnlyStrings($value)) {
            throw new \InvalidArgumentException(
                'Invalid header value; must be a string or array of strings'
            );
        }
        
        HttpSecurity::assertValidName($name);
        self::assertValidHeaderValue($value);
        
        return [$name, $value];
    }
    
    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function addHeader($name, $value): self
    {
        $normalizedHeader = self::normalizeHeader($name);
        $header = $this->headerNames[$normalizedHeader];
        $this->headers[$header] = array_merge((array)$this->headers[$header], $value);
        
        return $this;
    }
    
    /**
     * @param        $name
     * @param string $host
     * @return array|mixed
     */
    public function getHeader($name, $host = '')
    {
        if (!$this->hasHeader($name)) {
            if (self::normalizeHeader($name) === 'host'
                && ($this->uri && !$this->uri->getHost())
            ) {
                return [$host];
            }
            
            return [];
        }
        
        $headerName = $this->headerNames[self::normalizeHeader($name)];
        $header = $this->headers[$headerName];
        $header = (array)$header;
        
        return $header;
    }
    
    /**
     * @param $name
     * @return bool
     */
    public function hasHeader($name): bool
    {
        return array_key_exists(self::normalizeHeader($name), $this->headerNames);
    }
    
    /**
     * @return array|mixed
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * @param array $headers
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setHeaders(array $headers)
    {
        [$this->headerNames, $headers] = self::filterHeaders($headers);
        $this->assertHeaders($headers);
        
        $this->headers = $headers;
        
        return $this;
    }
    
    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setHeader($name, $value)
    {
        $normalizedHeader = self::normalizeHeader($name);
        if ($this->hasHeader($name)) {
            unset($this->headers[$this->headerNames[$normalizedHeader]]);
        }
        
        $this->headerNames[$normalizedHeader] = $name;
        $this->headers[$name] = $value;
        
        return $this;
    }
    
    /**
     * @param $value
     */
    public function setHost($value): void
    {
        $this->headerNames['host'] = 'Host';
        $this->headers['Host'] = [$value];
    }
    
    /**
     * @param UriInterface $uri
     */
    public function setUri(UriInterface $uri): void
    {
        $this->uri = $uri;
    }
    
    /**
     * @return array
     */
    public function map(): array
    {
        return array_map(
            function ($value, $key) {
                return $key . ': ' . $value[0];
            },
            $this->headers,
            array_keys($this->headers)
        );
    }
    
    /**
     * @param string $content
     * @return array
     */
    public function parse(string $content): array
    {
        $headers = [];
        
        if (empty($content)) {
            return $headers;
        }
        
        $filterContent = array_filter(explode("\r\n", $content));
    
        array_shift($filterContent);
     
        foreach ($filterContent as $field) {
            if (!is_array($field)) {
                $field = array_map('trim', explode(':', $field, 2));
                $headers[$field[0]] = $field[1];
            }
        }
        
        return $headers;
    }
    
    /**
     * @param $name
     * @return $this
     */
    public function removeHeader($name): self
    {
        $normalizedHeader = self::normalizeHeader($name);
        $header = $this->headerNames[$normalizedHeader];
        unset($this->headers[$header], $this->headerNames[$normalizedHeader]);
        
        return $this;
    }
    
    /**
     * @param array $headers
     * @throws \InvalidArgumentException
     */
    private function assertHeaders(array $headers): void
    {
        foreach ($headers as $name => $headerValues) {
            HttpSecurity::assertValidName($name);
            array_walk($headerValues, __NAMESPACE__ . '\Util\HttpSecurity::assertValid');
        }
    }
    
    /**
     * @param array $array
     * @return mixed
     */
    private static function arrayContainsOnlyStrings(array $array)
    {
        return array_reduce($array, [__CLASS__, 'filterStringValue'], true);
    }
    
    /**
     * @param array $values
     */
    private static function assertValidHeaderValue(array $values): void
    {
        array_walk($values, __NAMESPACE__ . '\Util\HttpSecurity::assertValid');
    }
    
    /**
     * @param $value
     * @throws \InvalidArgumentException
     */
    private static function assertHeaderValueType($value): void
    {
        if (!is_array($value) && !is_string($value) && !is_numeric($value)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid header value type; expected number, string, or array; received %s',
                (is_object($value) ? get_class($value) : gettype($value))
            ));
        }
        
        if (is_array($value)) {
            array_walk($value, function ($item) {
                if (!is_string($item) && !is_numeric($item)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Invalid header value type; expected number, string, or array; received %s',
                        (is_object($item) ? get_class($item) : gettype($item))
                    ));
                }
            });
        }
    }
    
    /**
     * @param array $originalHeaders
     * @return array
     * @throws \InvalidArgumentException
     */
    private static function filterHeaders(array $originalHeaders): array
    {
        $headerNames = $headers = [];
        foreach ($originalHeaders as $header => $value) {
            if (!is_string($header)) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid header name; expected non-empty string, received %s',
                    gettype($header)
                ));
            }
            
            self::assertHeaderValueType($value);
            
            $value = (array)$value;
            
            $headerNames[self::normalizeHeader($header)] = $header;
            $headers[$header] = $value;
        }
        
        return [$headerNames, $headers];
    }
    
    /**
     * @param $carry
     * @param $item
     * @return string|bool
     */
    private static function filterStringValue($carry, $item)
    {
        if (!is_string($item)) {
            return false;
        }
        
        return $carry;
    }
}
