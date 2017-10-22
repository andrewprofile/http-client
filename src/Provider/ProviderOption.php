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

interface ProviderOption
{
    public function setOption(int $option, callable $value): bool;
    public function setOptions(array $options): bool;
}
