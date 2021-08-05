<?php

return [
    'directories' => [
        __DIR__ . '/../src/Ajax' => [
            'namespace' => 'Lagdo\\Adminer\\Ajax',
            'autoload' => false,
            \Lagdo\Adminer\Ajax\Import::class => [
                'executeSqlFiles' => [
                    'upload' => "'adminer-import-sql-files-input'",
                ]
            ],
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
        Lagdo\Adminer\Db\Proxy::class => function($di) {
            $package = $di->get(Lagdo\Adminer\Package::class);
            return new Lagdo\Adminer\Db\Proxy($package);
        },
    ],
];
