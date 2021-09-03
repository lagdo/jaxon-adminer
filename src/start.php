<?php

$di = \jaxon()->di();
// Register the translator in the dependency container
$di->auto(Lagdo\DbAdmin\Translator::class);

// Register the db classes in the dependency container
$di->set(Lagdo\DbAdmin\Db\Db::class, function($di) {
    $package = $di->get(Lagdo\DbAdmin\Package::class);
    $server = $di->get('adminer_config_server'); // The selected server.
    return new Lagdo\DbAdmin\Db\Db($package->getServerOptions($server));
});
$di->auto(Lagdo\DbAdmin\Db\Util::class);

// Aliases for interfaces
$di->alias(Lagdo\DbAdmin\Driver\DbInterface::class, Lagdo\DbAdmin\Db\Db::class);
$di->alias(Lagdo\DbAdmin\Driver\UtilInterface::class, Lagdo\DbAdmin\Db\Util::class);

// Database specific classes
$di->set(Lagdo\DbAdmin\Driver\Db\ServerInterface::class, function($di) {
    $package = $di->get(Lagdo\DbAdmin\Package::class);
    $server = $di->get('adminer_config_server'); // The selected server.
    // The above key is defined by the corresponding plugin package.
    return $di->get('adminer_server_' . $package->getServerDriver($server));
});
$di->set(Lagdo\DbAdmin\Driver\Db\DriverInterface::class, function($di) {
    $server = $di->get(Lagdo\DbAdmin\Driver\Db\ServerInterface::class);
    return $server->driver();
});
$di->set(Lagdo\DbAdmin\Driver\Db\ConnectionInterface::class, function($di) {
    $server = $di->get(Lagdo\DbAdmin\Driver\Db\ServerInterface::class);
    return $server->connection();
});
