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
use HttpClient\Provider\DummyProvider;
use HttpClient\Request\RequestMethod;
use HttpClient\Request\RequestOptions;
use HttpClient\Uri\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

final class ClientTest extends TestCase
{
    public function testSendRequest(): void
    {
        $client = new Client(new DummyProvider());
        $options = new RequestOptions([]);
        $response = $client->get(new Uri('/foo'), $options);
    
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame('application/json; charset=utf8', $response->getHeaderLine('content-type'));
    }
    
    public function testCanMergeOnBaseUri(): void
    {
        $provider = new DummyProvider();
        $client = new Client($provider);
        $options = new RequestOptions([
            RequestOptions::BASE_URI => new Uri('http://foo.com/bar/'),
        ]);
        
        $client->get(new Uri('baz'), $options);
        
        $this->assertEquals(
            'http://foo.com/bar/baz',
            (string) $provider->getUri()
        );
    }
    
    public function testCanUseRelativeUriWithSend(): void
    {
        $provider = new DummyProvider();
        $client = new Client($provider);
        $options = new RequestOptions([
            RequestOptions::BASE_URI => new Uri('http://bar.com'),
        ]);
        
        $this->assertEquals('http://bar.com', (string) $options->getBaseUri());
        
        $client->request(RequestMethod::GET, new Uri('/baz'), $options);
        
        $this->assertEquals(
            'http://bar.com/baz',
            (string) $provider->getUri()
        );
    }
    
    public function testCanMergeOnBaseUriWithRequest(): void
    {
        $provider = new DummyProvider();
        $client = new Client($provider);
        $options1 = new RequestOptions([
            RequestOptions::BASE_URI => new Uri('http://bar.com/foo/'),
        ]);
        
        $client->request(RequestMethod::GET, new Uri('baz'), $options1);
        
        $this->assertEquals(
            'http://bar.com/foo/baz',
            (string) $provider->getUri()
        );
    
        $options2 = new RequestOptions([
            RequestOptions::BASE_URI => new Uri('http://example.com/foo/'),
        ]);
        
        $client->request(RequestMethod::GET, new Uri('baz'), $options2);
        
        $this->assertEquals(
            'http://example.com/foo/baz',
            (string) $provider->getUri()
        );
    }
    
    public function testUseAuthWithRequest(): void
    {
        $provider = new DummyProvider();
        $client = new Client($provider);
        $options1 = new RequestOptions([
            RequestOptions::BASE_URI => new Uri('http://bar.com/foo/'),
            RequestOptions::AUTH => ['username', 'password'],
        ]);
        
        $client->get(new Uri('baz'), $options1);
        
        $this->assertEquals(
            'username:password',
            $provider->getAuthData()
        );
    
        $options2 = new RequestOptions([
            RequestOptions::BASE_URI => new Uri('http://bar.com/foo/'),
            RequestOptions::AUTH => ['username', 'password', CurlAuthMethod::BASIC],
        ]);
    
        $client->get(new Uri('baz'), $options2);
        
        $this->assertEquals(
            CurlAuthMethod::BASIC,
            $provider->getAuthMethod()
        );
    }
    
    public function testSendWithJsonBody(): void
    {
        $provider = new DummyProvider();
        $client = new Client($provider);
        $options = new RequestOptions([
            RequestOptions::BASE_URI => new Uri('http://bar.com/'),
            RequestOptions::AUTH => ['username', 'password'],
            RequestOptions::JSON_BODY => true,
            RequestOptions::BODY => [
                'name' => 'foo'
            ]
        ]);
    
        $client->get(new Uri('/baz'), $options);
        
        $this->assertEquals(
            '{"name":"foo"}',
            $provider->getBody()
        );
    }
}
