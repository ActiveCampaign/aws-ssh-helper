<?php

namespace Bangpound\Assh\Provider;

use Aws\Ec2\Ec2Client;
use Bangpound\Assh\Command\GenerateSshConfigCommand;
use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use Pimple\ServiceProviderInterface;
use Twig\Environment;

class AsshServiceProvider implements ServiceProviderInterface
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
        $pimple[GenerateSshConfigCommand::class] = function (Container $c) {
            return new GenerateSshConfigCommand($c['assh.service_locator']);
        };

        $pimple['assh.service_locator'] = function (Container $c) {
            return new ServiceLocator($c, [
                Ec2Client::class => Ec2Client::class,
                Environment::class => Environment::class,
            ]);
        };
    }
}
