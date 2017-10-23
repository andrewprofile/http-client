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

class HeadersFilter
{
    /**
     * @param array $headers
     * @throws \InvalidArgumentException
     */
    public static function assertHeaders(array $headers): void
    {
        foreach ($headers as $name => $headerValues) {
            HttpSecurity::assertValidName($name);
            array_walk($headerValues, __NAMESPACE__ . '\Util\HttpSecurity::assertValid');
        }
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
     * @param array $originalHeaders
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function filterHeaders(array $originalHeaders): array
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
            
            $headerNames[Headers::normalizeHeader($header)] = $header;
            $headers[$header] = $value;
        }
        
        return [$headerNames, $headers];
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
     * @param array $values
     */
    private static function assertValidHeaderValue(array $values): void
    {
        array_walk($values, __NAMESPACE__ . '\Util\HttpSecurity::assertValid');
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
