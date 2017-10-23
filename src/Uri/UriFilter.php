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

use HttpClient\Util\HttpSecurity;

class UriFilter
{
    /**
     * @param $fragment
     * @throws \InvalidArgumentException
     */
    public static function assertFragmentType($fragment): void
    {
        if (!is_string($fragment)) {
            throw new \InvalidArgumentException(sprintf(
                '%s expects a string argument; received %s',
                __METHOD__,
                (is_object($fragment) ? get_class($fragment) : gettype($fragment))
            ));
        }
    }
    
    /**
     * @param $host
     * @throws \InvalidArgumentException
     */
    public static function assertHostType($host): void
    {
        if (!is_string($host)) {
            throw new \InvalidArgumentException(sprintf(
                '%s expects a string argument; received %s',
                __METHOD__,
                (is_object($host) ? get_class($host) : gettype($host))
            ));
        }
    }
    
    /**
     * @param $query
     * @throws \InvalidArgumentException
     */
    public static function assertQueryValue($query): void
    {
        if (!is_string($query)) {
            throw new \InvalidArgumentException(
                'Query string must be a string'
            );
        }
        
        if (strpos($query, '#') !== false) {
            throw new \InvalidArgumentException(
                'Query string must not include a URI fragment'
            );
        }
    }
    
    /**
     * @param null|string $path
     * @throws \InvalidArgumentException
     */
    public static function assertPathValue(?string $path): void
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException(
                'Invalid path provided; must be a string'
            );
        }
        
        if (strpos($path, '?') !== false) {
            throw new \InvalidArgumentException(
                'Invalid path provided; must not contain a query string'
            );
        }
        
        if (strpos($path, '#') !== false) {
            throw new \InvalidArgumentException(
                'Invalid path provided; must not contain a URI fragment'
            );
        }
    }
    
    /**
     * @param $port
     * @throws \InvalidArgumentException
     */
    public static function assertPortValue($port): void
    {
        if (!is_numeric($port) && $port !== null) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid port "%s" specified; must be an integer, an integer string, or null',
                (is_object($port) ? get_class($port) : gettype($port))
            ));
        }
        
        if ($port !== null) {
            if ($port < 1 || $port > 65535) {
                throw new \InvalidArgumentException(sprintf(
                    'Invalid port "%d" specified; must be a valid TCP/UDP port',
                    $port
                ));
            }
        }
    }
    
    /**
     * @param $scheme
     * @throws \InvalidArgumentException
     */
    public static function assertSchemeType($scheme): void
    {
        if (!is_string($scheme)) {
            throw new \InvalidArgumentException(sprintf(
                '%s expects a string argument; received %s',
                __METHOD__,
                (is_object($scheme) ? get_class($scheme) : gettype($scheme))
            ));
        }
    }
    
    /**
     * @param $user
     * @throws \InvalidArgumentException
     */
    public static function assertUserInfoType($user): void
    {
        if (!is_string($user)) {
            throw new \InvalidArgumentException(sprintf(
                '%s expects a string user argument; received %s',
                __METHOD__,
                (is_object($user) ? get_class($user) : gettype($user))
            ));
        }
    }
    
    /**
     * @param string $scheme
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function filterScheme(string $scheme): string
    {
        $scheme = strtolower($scheme);
        $scheme = preg_replace('#:(//)?$#', '', $scheme);
        if (empty($scheme)) {
            return '';
        }
        
        if (!array_key_exists($scheme, HttpSecurity::$allowedSchemes)) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported scheme "%s"; must be any empty string or in the set (%s)',
                $scheme,
                implode(', ', array_keys(HttpSecurity::$allowedSchemes))
            ));
        }
        
        return $scheme;
    }
    
    /**
     * @param string $path
     * @return string
     */
    public static function filterPath(string $path): string
    {
        $path = preg_replace_callback(
            '/(?:[^' . HttpSecurity::CHAR_UNRESERVED . ':@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            function ($matches) {
                return rawurlencode($matches[0]);
            },
            $path
        );
        
        if (empty($path)) {
            return $path;
        }
        
        if ($path[0] !== '/') {
            return $path;
        }
        
        return '/' . ltrim($path, '/');
    }
    
    /**
     * @param string $query
     * @return string
     */
    public static function filterQuery(string $query): string
    {
        if (!empty($query) && strpos($query, '?') === 0) {
            $query = (string) substr($query, 1);
        }
        
        $parts = explode('&', $query);
        foreach ($parts as $index => $part) {
            [$key, $value] = self::splitQueryValue($part);
            $key = self::filterQueryOrFragment($key);
            if ($value === null) {
                $parts[$index] = $key;
                continue;
            }
            
            $parts[$index] = sprintf(
                '%s=%s',
                $key,
                self::filterQueryOrFragment($value)
            );
        }
        
        return implode('&', $parts);
    }
    
    /**
     * @param string $fragment
     * @return string
     */
    public static function filterFragment(string $fragment): string
    {
        if (!empty($fragment) && strpos($fragment, '#') === 0) {
            $fragment = '%23' . substr($fragment, 1);
        }
        
        return self::filterQueryOrFragment($fragment);
    }
    
    /**
     * @param string   $scheme
     * @param string   $host
     * @param int|null $port
     * @return bool
     */
    public static function isNonStandardPort(string $scheme, string $host, ?int $port): bool
    {
        if (!$scheme) {
            return true;
        }
        
        if (!$host || $port === null) {
            return false;
        }
        
        return !isset(HttpSecurity::$allowedSchemes[$scheme]) || $port !== HttpSecurity::$allowedSchemes[$scheme];
    }
    
    /**
     * @param string $value
     * @return string
     */
    private static function filterQueryOrFragment(string $value): string
    {
        return preg_replace_callback(
            '/(?:[^'
            . HttpSecurity::CHAR_UNRESERVED . HttpSecurity::CHAR_SUB_DELIMS .
            '%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            function ($matches) {
                return rawurlencode($matches[0]);
            },
            $value
        );
    }
    
    /**
     * @param string $value
     * @return array
     */
    private static function splitQueryValue(string $value): array
    {
        $data = explode('=', $value, 2);
        if (count($data) === 1) {
            $data[] = null;
        }
        
        return $data;
    }
}
