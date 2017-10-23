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

final class UriBuilder
{
    /**
     * @var Uri|null
     */
    private $baseUri;
    
    /**
     * @var Uri
     */
    private $uri;
    
    /**
     * UriBuilder constructor.
     * @param Uri|null $baseUri
     * @param Uri      $uri
     */
    public function __construct(?Uri $baseUri, Uri $uri)
    {
        $this->baseUri = $baseUri;
        $this->uri = $uri;
    }
    
    /**
     * @return Uri
     */
    public function build(): Uri
    {
        $uriWithBaseUri = $this->uri;
        if ($this->baseUri instanceof Uri) {
            $uriResolver = new UriResolver();
            $uriWithBaseUri = $uriResolver($this->baseUri, $this->uri);
        }
        
        return $uriWithBaseUri;
    }
    
    public static function createUriString($scheme, $authority, $path, $queryWithFragment): string
    {
        $uri = '';
        if (!empty($scheme)) {
            $uri .= $scheme . ':';
        }
    
        if (!empty($authority)) {
            $uri .= '//';
            if ('file' === $scheme && 0 === strpos($authority, 'localhost')) {
                $authority = substr($authority, 9);
            }
            $uri .= $authority;
        }
    
        if ($path) {
            if (empty($path) || '/' !== $path[0]) {
                $path = '/' . $path;
            }
            $uri .= $path;
        }
    
        if ($queryWithFragment) {
            $uri .= $queryWithFragment;
        }
        
        return $uri;
    }
    
    public static function createQueryWithFragment(string $query, string $fragment): string
    {
        $queryWithFragment = '';
        if ($query) {
            $queryWithFragment .= sprintf('?%s', $query);
        }
    
        if ($fragment) {
            $queryWithFragment .= sprintf('#%s', $fragment);
        }
        
        return $queryWithFragment;
    }
}
