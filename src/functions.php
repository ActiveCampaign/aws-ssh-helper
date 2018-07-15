<?php

namespace Bangpound\Assh;

use Pimple;
use Psr\Container\ContainerInterface;

/**
 * @param array $values
 *
 * @return ContainerInterface
 */
function get_container(array $values = []): ContainerInterface
{
    $c = new Pimple\Container();

    $c->register(new Provider\EventDispatcherServiceProvider());
    $c->register(new Provider\ConsoleServiceProvider());
    $c->register(new Provider\LoggerServiceProvider());
    $c->register(new Provider\GuzzleServiceProvider());
    $c->register(new Provider\AwsServiceProvider());
    $c->register(new Provider\TwigServiceProvider());
    $c->register(new Provider\AsshServiceProvider());

    foreach ($values as $key => $value) {
        $c->offsetSet($key, $value);
    }

    return new Pimple\Psr11\Container($c);
}
