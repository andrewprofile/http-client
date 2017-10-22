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

namespace HttpClient\Response;

use HttpClient\Headers;
use HttpClient\Stream;
use HttpClient\Uri\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response extends AbstractMessage implements ResponseInterface
{
    const MIN_STATUS_CODE = 100;
    const MAX_STATUS_CODE = 599;
    
    /**
     * @var array
     */
    private static $phrases = [
        // INFORMATIONAL CODES
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // SUCCESS CODES
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        // REDIRECTION CODES
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // Deprecated. Originally meant "Subsequent requests should use the specified proxy."
        307 => 'Temporary Redirect',
        // CLIENT ERROR
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Headers Fields Too Large',
        // SERVER ERROR
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    ];
    
    /**
     * @var string
     */
    private $reasonPhrase = 'OK';
    
    /**
     * @var int
     */
    private $statusCode;
    
    /**
     * Response constructor.
     * @param string|null|resource|StreamInterface $body
     * @param int                                  $status
     * @param array                                $headers
     * @throws \InvalidArgumentException
     */
    public function __construct($body = 'php://memory', int $status = 200, array $headers = [])
    {
        if (!is_string($body) && !is_resource($body) && !$body instanceof StreamInterface) {
            throw new \InvalidArgumentException(
                'Stream must be a string stream resource identifier, '
                . 'an actual stream resource, '
                . 'or a Psr\Http\Message\StreamInterface implementation'
            );
        }
        
        $this->setStatusCode($status);
        $this->stream = ($body instanceof StreamInterface) ? $body : new Stream($body, 'wb+');
        $this->headers = new Headers(new Uri(), $headers);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    
    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = ''): self
    {
        $self = clone $this;
        $self->setStatusCode($code);
        $self->reasonPhrase = $reasonPhrase;
        
        return $self;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase(): string
    {
        if ($this->reasonPhrase === 'OK' || isset(self::$phrases[$this->statusCode])) {
            $this->reasonPhrase = self::$phrases[$this->statusCode];
        }
        
        return $this->reasonPhrase;
    }
    
    /**
     * @param int $code
     * @throws \InvalidArgumentException
     */
    private function setStatusCode(int $code): void
    {
        if ($code < static::MIN_STATUS_CODE || $code > static::MAX_STATUS_CODE) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid status code "%s"; must be an integer between 100 and 599, inclusive',
                (is_scalar($code) ? $code : gettype($code))
            ));
        }
        
        $this->statusCode = $code;
    }
}
