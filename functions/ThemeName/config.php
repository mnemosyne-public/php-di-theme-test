<?php

use Psr\Container\ContainerInterface;
use function DI\create;

use ThemeName\Constants;
use ThemeName\Layout\Page;
use ThemeName\Theme\Api;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

return [
    'settings' => [
        'templates' => Constants::getPath('app.base', 'Templates'),
        'env' => [
            'debug' => true, 
            'auto_reload' => true,
            'autoescape' => 'html',
        ],
    ],

    'twig' => function (ContainerInterface $container) {
        $templates = $container->get('settings')['templates'];
        $env = $container->get('settings')['env'];
        $twig = new Environment(new FilesystemLoader($templates), $env);
        return $twig;
    },

    'Page' => function (ContainerInterface $container) {
        return (new Page($container->get('post'), $container->get('twig')))->render();
    },

    'Api' => function (ContainerInterface $container) {
        return new Api($container->get('twig'));
    },
];