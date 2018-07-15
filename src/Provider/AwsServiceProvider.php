<?php

namespace Bangpound\Assh\Provider;

use Aws\Ec2\Ec2Client;
use Aws\Handler\GuzzleV6\GuzzleHandler;
use Aws\Sdk;
use GuzzleHttp\ClientInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Console\Input\InputInterface;

class AwsServiceProvider implements ServiceProviderInterface
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
        $pimple['aws.region'] = 'us-east-1';
        $pimple['aws.profile'] = 'default';

        $pimple[GuzzleHandler::class] = function (Container $c) {
            return new GuzzleHandler($c[ClientInterface::class]);
        };

        $pimple[Sdk::class] = function (Container $c) {
            return new Sdk([
                'http_handler' => $c[GuzzleHandler::class],
            ]);
        };

        $pimple[Ec2Client::class] = function (Container $c) {
            return $c[Sdk::class]->createClient('ec2', [
                'version' => '2016-11-15',
                'region' => $c[InputInterface::class]->getParameterOption('--region') ?: $c['aws.region'],
                'profile' => $c[InputInterface::class]->getParameterOption('--profile') ?: $c['aws.profile'],
            ]);
        };
    }
}
