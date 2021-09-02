<?php

$di = \jaxon()->di();
// Register the translator in the dependency container
$di->set(Lagdo\Adminer\Translator::class, function() {
    return new Lagdo\Adminer\Translator();
});

// Register the db classes in the dependency container
$di->set(Lagdo\Adminer\Db\Db::class, function($di) {
    $server = $di->get('adminer_config_server'); // The selected server.
    $package = $di->get(Lagdo\Adminer\Package::class);
    return new Lagdo\Adminer\Db\Db($package->getServerOptions($server));
});
$di->set(Lagdo\Adminer\Db\Util::class, function($di) {
    return new Lagdo\Adminer\Db\Util(
        $di->get(Lagdo\Adminer\Db\Db::class),
        $di->get(Lagdo\Adminer\Translator::class));
});

// Aliases for interfaces
$di->alias(Lagdo\Adminer\Driver\DbInterface::class, Lagdo\Adminer\Db\Db::class);
$di->alias(Lagdo\Adminer\Driver\UtilInterface::class, Lagdo\Adminer\Db\Util::class);

// Database specific classes
$di->set(Lagdo\DbAdmin\Driver\Db\ServerInterface::class, function($di) {
    $server = $di->get('adminer_config_server'); // The selected server.
    $package = $di->get(Lagdo\Adminer\Package::class);
    $options = $package->getServerOptions($server);
    return $di->get('adminer_server_' . $options['driver']);
});
$di->set(Lagdo\DbAdmin\Driver\Db\DriverInterface::class, function($di) {
    $server = $di->get(Lagdo\DbAdmin\Driver\Db\ServerInterface::class);
    return $server->driver();
});
$di->set(Lagdo\DbAdmin\Driver\Db\ConnectionInterface::class, function($di) {
    $server = $di->get(Lagdo\DbAdmin\Driver\Db\ServerInterface::class);
    return $server->connection();
});
