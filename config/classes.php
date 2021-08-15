<?php

return [
    Lagdo\Adminer\Ajax\Import::class => [
        'executeSqlFiles' => [
            'upload' => "'adminer-import-sql-files-input'",
        ],
    ],
    Lagdo\Adminer\Ajax\Table::class => [
        'show,add,edit' => [
            '__after' => 'showBreadcrumbs',
        ]
    ],
    Lagdo\Adminer\Ajax\View::class => [
        'show' => [
            '__after' => 'showBreadcrumbs',
        ]
    ],
    Lagdo\Adminer\Ajax\Table\Query::class => [
        'showInsert,showUpdate' => [
            '__after' => 'showBreadcrumbs',
        ]
    ],
    Lagdo\Adminer\Ajax\Table\Select::class => [
        'show' => [
            '__after' => 'showBreadcrumbs',
        ]
    ],
    Lagdo\Adminer\Ajax\Server::class => [
        'connect' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['.menu-action-databases', 'adminer-database-menu'],
            ],
        ],
        'showServer' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['.menu-action-databases', 'adminer-database-menu'],
            ],
        ],
        'showDatabases' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['.menu-action-databases', 'adminer-database-menu'],
            ],
        ],
        'showPrivileges' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['.menu-action-privileges', 'adminer-database-menu'],
            ],
        ],
        'showProcesses' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['.menu-action-processes', 'adminer-database-menu'],
            ],
        ],
        'showVariables' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['.menu-action-variables', 'adminer-database-menu'],
            ],
        ],
        'showStatus' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['.menu-action-status', 'adminer-database-menu'],
            ],
        ],
    ],
    Lagdo\Adminer\Ajax\Database::class => [
        'select' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['.menu-action-table', 'adminer-database-menu'],
            ],
        ],
        'showTables' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['.menu-action-table', 'adminer-database-menu'],
            ],
        ],
        'showViews' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['.menu-action-view', 'adminer-database-menu'],
            ],
        ],
        'showRoutines' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['.menu-action-routine', 'adminer-database-menu'],
            ],
        ],
        'showSequences' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['.menu-action-sequence', 'adminer-database-menu'],
            ],
        ],
        'showUserTypes' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['.menu-action-type', 'adminer-database-menu'],
            ],
        ],
        'showEvents' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['.menu-action-event', 'adminer-database-menu'],
            ],
        ],
    ],
    Lagdo\Adminer\Ajax\Command::class => [
        'showServerForm' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['#adminer-menu-action-server-command', 'adminer-server-actions'],
            ],
        ],
        'showDatabaseForm' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['#adminer-menu-action-database-command', 'adminer-database-actions'],
            ],
        ],
    ],
    Lagdo\Adminer\Ajax\Import::class => [
        'showServerForm' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['#adminer-menu-action-server-import', 'adminer-server-actions'],
            ],
        ],
        'showDatabaseForm' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['#adminer-menu-action-database-import', 'adminer-database-actions'],
            ],
        ],
    ],
    Lagdo\Adminer\Ajax\Export::class => [
        'showServerForm' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['#adminer-menu-action-server-export', 'adminer-server-actions'],
            ],
        ],
        'showDatabaseForm' => [
            '__after' => [
                'showBreadcrumbs',
                'selectMenuItem' => ['#adminer-menu-action-database-export', 'adminer-database-actions'],
            ],
        ],
    ],
];
