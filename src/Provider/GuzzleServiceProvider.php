<?php

namespace Bangpound\Assh\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GuzzleServiceProvider implements ServiceProviderInterface
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
        $pimple[HandlerStack::class] = function (Container $c) {
            return HandlerStack::create();
        };

        $pimple[MessageFormatter::class] = function (Container $c) {
            $map = [
                OutputInterface::VERBOSITY_VERBOSE => MessageFormatter::SHORT,
                OutputInterface::VERBOSITY_VERY_VERBOSE => MessageFormatter::CLF,
                OutputInterface::VERBOSITY_DEBUG => MessageFormatter::DEBUG,
            ];
            $template = $map[$c[OutputInterface::class]->getVerbosity()] ?? MessageFormatter::SHORT;
            return new MessageFormatter($template);
        };

        $pimple['guzzle.middleware.log'] = function (Container $c) {
            return Middleware::log($c[LoggerInterface::class], $c[MessageFormatter::class]);
        };

        $pimple->extend(HandlerStack::class, function (HandlerStack $handlerStack, Container $c) {
            if ($c[OutputInterface::class]->isVerbose()) {
                $handlerStack->push($c['guzzle.middleware.log']);
            }

            return $handlerStack;
        });

        $pimple[Client::class] = function (Container $c) {
            return new Client([
                'handler' => $c[HandlerStack::class],
            ]);
        };

        $pimple[ClientInterface::class] = function (Container $c) {
            return $c[Client::class];
        };
    }
}
