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

namespace HttpClient\Uri;

final class UriResolver
{
    /**
     * @param Uri $baseUri
     * @param Uri $uri
     * @return Uri
     * @throws \InvalidArgumentException
     */
    public function __invoke(Uri $baseUri, Uri $uri): Uri
    {
        return new Uri(UriBuilder::createUriString(
            $baseUri->getScheme(),
            $baseUri->getAuthority(),
            $baseUri->getPath().$uri->getPath(),
            UriBuilder::createQueryWithFragment($uri->getQuery(), $uri->getFragment())
        ));
    }
}
