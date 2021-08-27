<?php

use Lagdo\Adminer\DbAdmin;

DbAdmin::addServer("mysql", Lagdo\Adminer\Drivers\Db\MySql\Server::class);
DbAdmin::addServer("pgsql", Lagdo\Adminer\Drivers\Db\PgSql\Server::class);
DbAdmin::addServer("oracle", Lagdo\Adminer\Drivers\Db\Oracle\Server::class);
DbAdmin::addServer("mssql", Lagdo\Adminer\Drivers\Db\MsSql\Server::class);
DbAdmin::addServer("sqlite", Lagdo\Adminer\Drivers\Db\Sqlite\Server::class);
DbAdmin::addServer("mongo", Lagdo\Adminer\Drivers\Db\Mongo\Server::class);
DbAdmin::addServer("elastic", Lagdo\Adminer\Drivers\Db\Elastic\Server::class);
