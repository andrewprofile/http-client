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

namespace HttpClient\Provider\Curl;

final class CurlAuthMethod
{
    const BASIC = CURLAUTH_BASIC;
    const DIGEST = CURLAUTH_DIGEST;
    const NTLM = CURLAUTH_NTLM;
}
