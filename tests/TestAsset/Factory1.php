<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Laminas\Config\TestAsset;

final class Factory1
{
    public function __invoke()
    {
        return new Invokable1();
    }
}
