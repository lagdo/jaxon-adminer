<?php

return [
    'directories' => [
        __DIR__ . '/../ajax' => [
            'namespace' => 'Lagdo\\Adminer\\Ajax',
            'autoload' => false,
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
                'default' => 'bootstrap',
            ],
        ],
        // 'adminer_views' => [
        //     'directory' => __DIR__ . '/../templates/views/bootstrap',
        //     'extension' => '.html.twig',
        //     'renderer' => 'twig',
        // ],
    ],
    'container' => [
        Lagdo\Adminer\Db\Proxy::class => function() {
            return new Lagdo\Adminer\Db\Proxy();
        },
    ],
];
