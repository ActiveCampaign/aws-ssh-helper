<?php

namespace Bangpound\Assh\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventDispatcherServiceProvider implements ServiceProviderInterface
{

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        $pimple[EventDispatcher::class] = function (Container $c) {
            return new EventDispatcher();
        };

        $pimple[EventDispatcherInterface::class] = function (Container $c) {
            return $c[EventDispatcher::class];
        };
    }
}
