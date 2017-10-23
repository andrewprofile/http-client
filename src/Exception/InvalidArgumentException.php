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

namespace HttpClient\Exception;

class InvalidArgumentException extends \Exception
{
    /**
     * @param string $method
     * @return InvalidArgumentException
     */
    public static function jsonParsingError(string $method): self
    {
        return new self(sprintf('%s error: %s', $method, json_last_error_msg()));
    }
}
