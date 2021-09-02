<?php

$di = \jaxon()->di();
// Register the translator in the dependency container
$di->set(Lagdo\Adminer\Translator::class, function() {
    return new Lagdo\Adminer\Translator();
});

// Register the db classes in the dependency container
$di->set(Lagdo\Adminer\Db\Db::class, function($di) {
    $package = $di->get(Lagdo\Adminer\Package::class);
    $server = $di->get('adminer_config_server'); // The selected server.
    return new Lagdo\Adminer\Db\Db($package->getServerOptions($server));
});
$di->auto(Lagdo\Adminer\Db\Util::class);

// Aliases for interfaces
$di->alias(Lagdo\DbAdmin\Driver\DbInterface::class, Lagdo\Adminer\Db\Db::class);
$di->alias(Lagdo\DbAdmin\Driver\UtilInterface::class, Lagdo\Adminer\Db\Util::class);

// Database specific classes
$di->set(Lagdo\DbAdmin\Driver\Db\ServerInterface::class, function($di) {
    $package = $di->get(Lagdo\Adminer\Package::class);
    $server = $di->get('adminer_config_server'); // The selected server.
    // The above key is defined by the coresponding plugin package.
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
