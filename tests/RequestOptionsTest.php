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

use HttpClient\Provider\Curl\CurlAuthMethod;
use HttpClient\Request\RequestOptions;
use HttpClient\Uri\Uri;
use PHPUnit\Framework\TestCase;

final class RequestOptionsTest extends TestCase
{
    public function testSetRequestOptions(): void
    {
        $baseUri = new Uri('http://foo.com/');
        $options = new RequestOptions([
            RequestOptions::BASE_URI => $baseUri,
            RequestOptions::AUTH => ['username', 'password', CurlAuthMethod::BASIC],
            RequestOptions::HEADERS => new Headers($baseUri, [
                'Foo'  => 'bar'
            ]),
            RequestOptions::JSON_BODY => true,
            RequestOptions::BODY => [
                'foo' => [
                    'name' => 'bar',
                ],
            ]
        ]);
        
        $this->assertSame('http://foo.com/', (string) $options->getBaseUri());
        $this->assertSame(
            ['username', 'password', CurlAuthMethod::BASIC],
            $options->getAuth()
        );
        $this->assertSame(
            ['Foo'  => ['bar']],
            $options->getHeaders() ? $options->getHeaders()->getHeaders() : []
        );
        $this->assertNotFalse($options->isJson());
        $this->assertSame(
            ['foo' => ['name' => 'bar']],
            $options->getBody()
        );
    }
}
