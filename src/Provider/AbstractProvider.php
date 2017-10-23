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

use HttpClient\Request\RequestMethod;
use HttpClient\Request\RequestOptions;
use HttpClient\Uri\Uri;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractProvider implements Provider
{
    public function get(Uri $uri, RequestOptions $options): ResponseInterface
    {
        return $this->request(RequestMethod::GET, $uri, $options);
    }
    
    public function post(Uri $uri, RequestOptions $options): ResponseInterface
    {
        return $this->request(RequestMethod::POST, $uri, $options);
    }
}
