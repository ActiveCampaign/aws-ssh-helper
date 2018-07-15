<?php
/**
 * Created by PhpStorm.
 * User: bdoherty
 * Date: 7/15/18
 * Time: 11:51 AM
 */

namespace Bangpound\Assh\Provider;

use Bangpound\Assh\Twig\Extension;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Bridge\Twig\Command\LintCommand;
use Symfony\Bridge\Twig\Extension\YamlExtension;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

class TwigServiceProvider implements ServiceProviderInterface
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
        $pimple['twig.extensions'] = [
            Extension::class,
            YamlExtension::class,
        ];

        $pimple[FilesystemLoader::class] = function (Container $c) {
            return new FilesystemLoader($c['twig.path'], $c['app.root']);
        };

        $pimple[LoaderInterface::class] = function (Container $c) {
            return $c[FilesystemLoader::class];
        };

        $pimple[Environment::class] = function (Container $c) {
            return new Environment($c[LoaderInterface::class], [
                'autoescape' => 'name',
            ]);
        };

        $pimple[Extension::class] = function (Container $c) {
            return new Extension();
        };

        $pimple[YamlExtension::class] = function (Container $c) {
            return new YamlExtension();
        };

        $pimple[LintCommand::class] = function (Container $c) {
            return new LintCommand($c[Environment::class]);
        };

        $pimple->extend(Environment::class, function (Environment $twig, Container $c) {
            foreach ($c['twig.extensions'] as $name) {
                $twig->addExtension($c[$name]);
            }
            return $twig;
        });

        $pimple->extend('console.commands', function (array $commands, Container $c) {
            $commands[] = LintCommand::class;
            return $commands;
        });
    }
}
