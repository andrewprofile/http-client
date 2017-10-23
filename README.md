# HttpClient

This is PHP HTTP Client for easy send HTTP requests and integrate with web services.
Uses PSR-7 interfaces for responses, and streams.

## Requirements

HttpClient requires PHP version 7.1.0 or greater.

Usage: `psr/http-message`

Usage for development: `jakub-onderka/php-parallel-lint`, `phpstan/phpstan`, `squizlabs/php_codesniffer`, `phpmd/phpmd`,

`Php Inspections (EA Extended)`

## Installation

#### Production
```php
$ composer install --no-dev --optimize-autoloader
```

#### Development
```php
$ composer install
```

## Testing

```php
$ bin/phpunit
```

### Static Analysis

```php
$ bin/parallel-lint --exclude vendor .
```

```php
$ bin/phpstan analyse src tests -l 7
```

## Examples

#### Example 1

```php
$request = new Client(new CurlProvider());
$uri = new Uri('http://example.com/v1/producers');
$options = new RequestOptions([
    RequestOptions::AUTH => ['username', 'password'],
    RequestOptions::HEADERS => new Headers($uri, [
        'Content-Type'  => 'application/json; charset=utf8'
    ]),
]);

$response = $request->get($uri, $options);

echo $response->getStatusCode();
// "200"
echo $response->getHeaderLine('content-type');
// "application/json"
echo $response->getBody();
// "{"version":"v1","success":true,"data":{"producers":[ //...// ]},"error":null}"
```

#### Example 2

```php
$request = new Client(new CurlProvider());
$baseUri = new Uri('http://example.com/');
$options = new RequestOptions([
    RequestOptions::BASE_URI => $baseUri,
    RequestOptions::AUTH => ['username', 'password'],
]);

$response = $request->request(RequestMethod::GET, new Uri('/v1/producers'), $options);

echo $response->getStatusCode();
// "200"
echo $response->getHeaderLine('content-type');
// "application/json"
echo $response->getBody();
// "{"version":"v1","success":true,"data":{"producers":[ //...// ]},"error":null}"
```

#### Example 3

```php
$request = new Client(new CurlProvider());
$baseUri = new Uri('http://example.com/');
$options = new RequestOptions([
    RequestOptions::BASE_URI => $baseUri,
    RequestOptions::AUTH => ['username', 'password', CurlAuthMethod::BASIC],
    RequestOptions::HEADERS => new Headers($baseUri, [
      'Content-Type'  => 'application/json; charset=utf8'
    ]),
    RequestOptions::JSON_BODY => true,
    RequestOptions::BODY => [
        'producer' => [
            'name' => 'test',
            'site_url' => 'http:://httpclient.dev',
            'logo_filename' => 'logo.jpg',
            'ordering' => true,
            'source_id' => null,
        ],
    ]
]);

$response = $request->post(new Uri('/v1/producers'), $options);

echo $response->getStatusCode();
// "200"
echo $response->getHeaderLine('content-type');
// "application/json"
echo $response->getBody();
// "{"version":"v1","success":true,"data":{"producer":[ //...// ]},"error":null}"
```
