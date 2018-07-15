<?php

/**
 * @param array $argv
 *
 * @return int
 * @throws Exception
 */
function main(array $argv)
{
    $appRoot = dirname(__DIR__);
    if (file_exists($appRoot.'/vendor/autoload.php')) {
        $autoload = include_once $appRoot.'/vendor/autoload.php';
    } elseif (file_exists($appRoot.'/../../autoload.php')) {
        $autoload = include_once $appRoot.'/../../autoload.php';
    } else {
        echo 'Could not find autoloader; try running `composer install`.'.PHP_EOL;
        exit(1);
    }

    $c = Bangpound\Assh\get_container([
        'argv' => $argv,
        'autoloader' => $autoload,
        'app.root' => $appRoot,
        'aws.profile' => 'production',
        'aws.region' => 'us-east-1',
        'twig.path' => ['templates'],
    ]);

    return $c->get(Symfony\Component\Console\Application::class)->run(
        $c->get(Symfony\Component\Console\Input\InputInterface::class),
        $c->get(Symfony\Component\Console\Output\OutputInterface::class)
    );
}
