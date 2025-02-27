<?php

declare(strict_types=1);

namespace Chubbyphp\Tests\Laminas\Config\TestAsset;

final class Factory2
{
    public function __invoke()
    {
        return new Invokable2();
    }
}
