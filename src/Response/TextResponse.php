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

use HttpClient\Stream;

final class TextResponse extends Response
{
    /**
     * TextResponse constructor.
     * @param string $body
     * @param int    $status
     * @param array  $headers
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct(string $body, $status = 200, array $headers = [])
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write($body);
        $stream->rewind();
        
        parent::__construct($stream, $status, $headers);
    }
}
