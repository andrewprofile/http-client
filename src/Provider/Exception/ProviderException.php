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

namespace HttpClient\Provider\Exception;

final class ProviderException extends \Exception
{
    /**
     * @param string $provider
     * @return ProviderException
     */
    public static function notLoaded(string $provider): self
    {
        return new self(sprintf('%s extension is not loaded', $provider));
    }
}
