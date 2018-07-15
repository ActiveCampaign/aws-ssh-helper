<?php

namespace Bangpound\Assh\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoggerServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple): void
    {
        $pimple[LoggerInterface::class] = function (Container $c) {
            return $c[ConsoleLogger::class];
        };

        $pimple[ConsoleLogger::class] = function (Container $c) {
            $output = $c[OutputInterface::class];
            if ($output instanceof ConsoleOutputInterface) {
                $output = $output->getErrorOutput();
            }

            return new ConsoleLogger($output);
        };
    }
}
