<?php

return [
    'directories' => [
        __DIR__ . '/../app' => [
            'namespace' => 'Lagdo\\Adminer\\App',
            'autoload' => false,
            'classes' => require( __DIR__ . '/classes.php'),
        ],
    ],
    'views' => [
        'adminer::codes' => [
            'directory' => __DIR__ . '/../templates/codes',
            'extension' => '.php',
            'renderer' => 'jaxon',
        ],
        'adminer::views' => [
            'directory' => __DIR__ . '/../templates/views',
            'extension' => '.php',
            'renderer' => 'jaxon',
            'template' => [
                'option' => 'template',
                'default' => 'bootstrap3',
            ],
        ],
        // 'adminer_views' => [
        //     'directory' => __DIR__ . '/../templates/views/bootstrap',
        //     'extension' => '.html.twig',
        //     'renderer' => 'twig',
        // ],
    ],
    'container' => [
        Lagdo\Adminer\DbAdmin::class => function($di) {
            $package = $di->get(Lagdo\Adminer\Package::class);
            return new Lagdo\Adminer\DbAdmin($package);
        },
    ],
];
