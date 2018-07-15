<?php

namespace Bangpound\Assh\Provider;

use Bangpound\Assh\Command\GenerateSshConfigCommand;
use Pimple\Container;
use Pimple\Psr11\ServiceLocator;
use Pimple\ServiceProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use Symfony\Component\Console\EventListener\ErrorListener;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Yaml\Command\LintCommand as YamlLintCommand;

class ConsoleServiceProvider implements ServiceProviderInterface
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
        $pimple['console.commands'] = function (Container $c) {
            return [
                GenerateSshConfigCommand::class,
                YamlLintCommand::class,
            ];
        };

        $pimple['console.default_command'] = 'generate:ssh-config';

        $pimple[ArgvInput::class] = function (Container $c) {
            return new ArgvInput($c['argv']);
        };

        $pimple[InputInterface::class] = function (Container $c) {
            return $c[ArgvInput::class];
        };

        $pimple[ConsoleOutput::class] = function (Container $c) {
            return new ConsoleOutput();
        };

        $pimple[OutputInterface::class] = function (Container $c) {
            return $c[ConsoleOutput::class];
        };

        $pimple[Application::class] = function (Container $c) {
            return new Application('ASSH Helper', '@git_commit_short@');
        };

        $pimple[YamlLintCommand::class] = function (Container $c) {
            return new YamlLintCommand();
        };

        $pimple[ContainerCommandLoader::class] = function (Container $c) {
            $locator = new ServiceLocator($c, $c['console.commands']);

            $names = array_map(function (string $class) {
                $r = new \ReflectionClass($class);
                \assert($r->isSubclassOf(Command::class));
                return $class::getDefaultName();
            }, $c['console.commands']);

            return new ContainerCommandLoader($locator, array_combine($names, $c['console.commands']));
        };

        $pimple[CommandLoaderInterface::class] = function (Container $c) {
            return $c[ContainerCommandLoader::class];
        };

        $pimple[ErrorListener::class] = function (Container $c) {
            return new ErrorListener($c[LoggerInterface::class]);
        };

        $pimple->extend(EventDispatcherInterface::class, function (EventDispatcherInterface $dispatcher, Container $c) {
            $dispatcher->addSubscriber($c[ErrorListener::class]);
            return $dispatcher;
        });

        $pimple->extend(Application::class, function (Application $app, Container $c) {
            $app->setDispatcher($c[EventDispatcherInterface::class]);
            $app->setCommandLoader($c[CommandLoaderInterface::class]);

            if (!empty($c['console.default_command'])) {
                $app->setDefaultCommand($c['console.default_command'], true);
            } elseif (\count($c[CommandLoaderInterface::class]->getNames()) === 1) {
                $app->setDefaultCommand($c[CommandLoaderInterface::class]->getNames()[0], true);
            }

            return $app;
        });
    }
}
