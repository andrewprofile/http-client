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

namespace HttpClient\Util;

use HttpClient\Exception\InvalidArgumentException;

final class RequestUtil
{
    /**
     * @var array
     */
    private static $camelCache = [];
    
    /**
     * @param string $json
     * @param bool   $assoc
     * @param int    $depth
     * @param int    $options
     * @return iterable
     * @throws InvalidArgumentException
     */
    public static function jsonDecode(string $json, bool $assoc = false, int $depth = 512, int $options = 0): iterable
    {
        $data = \json_decode($json, $assoc, $depth, $options);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw InvalidArgumentException::jsonParsingError('json_decode');
        }
        
        if ($assoc === false) {
            return new class($data) implements \IteratorAggregate {
                public function __construct(\stdClass $object = null)
                {
                    foreach ((array)$object as $key => $value) {
                        $this->$key = $value;
                    }
                }
        
                public function getIterator(): \Traversable
                {
                    return new \ArrayIterator($this);
                }
            };
        }
        
        return $data;
    }
    
    /**
     * @param array|null $value
     * @param int        $options
     * @param int        $depth
     * @return string
     * @throws InvalidArgumentException
     */
    public static function jsonEncode(?array $value, int $options = 0, int $depth = 512): string
    {
        $json = \json_encode($value, $options, $depth);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw InvalidArgumentException::jsonParsingError('json_encode');
        }
        
        return $json;
    }
    
    /**
     * @param string $input
     * @param string $separator
     * @return string
     */
    public static function camelize(string $input, string $separator = '_'): string
    {
        if (isset(self::$camelCache[$input])) {
            return self::$camelCache[$input];
        }
        
        return self::$camelCache[$input] = lcfirst(self::studly($input, $separator));
    }
    
    /**
     * @param string $input
     * @param string $separator
     * @return string
     */
    private static function studly(string $input, string $separator = '_'): string
    {
        return str_replace($separator, '', ucwords($input, $separator));
    }
}
