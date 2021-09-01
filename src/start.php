<?php

// Register the translator in the dependency container
\jaxon()->di()->set(Lagdo\Adminer\Translator::class, function() {
    return new Lagdo\Adminer\Translator();
});

// Register the db classes in the dependency container
\jaxon()->di()->set(Lagdo\Adminer\Db\Db::class, function($di) {
    $server = $di->get('adminer_config_server');
    $package = $di->get(Lagdo\Adminer\Package::class);
    return new Lagdo\Adminer\Db\Db($package->getServerOptions($server));
});
\jaxon()->di()->set(Lagdo\Adminer\Db\Util::class, function($di) {
    return new Lagdo\Adminer\Db\Util(
        $di->get(Lagdo\Adminer\Db\Db::class),
        $di->get(Lagdo\Adminer\Translator::class));
});
// Aliases
\jaxon()->di()->set(Lagdo\Adminer\Driver\DbInterface::class, function($di) {
    return $di->get(Lagdo\Adminer\Db\Db::class);
});
\jaxon()->di()->set(Lagdo\Adminer\Driver\UtilInterface::class, function($di) {
    return $di->get(Lagdo\Adminer\Db\Util::class);
});
// Database specific classes
\jaxon()->di()->set(Lagdo\DbAdmin\Driver\Db\ServerInterface::class, function($di) {
    $server = $di->get('adminer_config_server');
    $package = $di->get(Lagdo\Adminer\Package::class);
    $options = $package->getServerOptions($server);
    return $di->get('adminer_server_' . $options['driver']);
});
\jaxon()->di()->set(Lagdo\DbAdmin\Driver\Db\DriverInterface::class, function($di) {
    $server = $di->get(Lagdo\DbAdmin\Driver\Db\ServerInterface::class);
    return $server->getDriver();
});
\jaxon()->di()->set(Lagdo\DbAdmin\Driver\Db\ConnectionInterface::class, function($di) {
    $server = $di->get(Lagdo\DbAdmin\Driver\Db\ServerInterface::class);
    return $server->getConnection();
});
