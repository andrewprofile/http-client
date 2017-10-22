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

use HttpClient\Request\RequestOptions;
use HttpClient\Uri\Uri;
use Psr\Http\Message\ResponseInterface;

interface Provider
{
    const VERSION = '1.0.0';
    
    /**
     * @param Uri            $uri
     * @param RequestOptions $options
     * @return ResponseInterface
     */
    public function get(Uri $uri, RequestOptions $options): ResponseInterface;
    
    /**
     * @param Uri            $uri
     * @param RequestOptions $options
     * @return ResponseInterface
     */
    public function post(Uri $uri, RequestOptions $options): ResponseInterface;
    
    /**
     * @param string         $method
     * @param Uri            $uri
     * @param RequestOptions $options
     * @return ResponseInterface
     */
    public function request(string $method, Uri $uri, RequestOptions $options): ResponseInterface;
}
