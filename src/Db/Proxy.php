<?php

namespace Lagdo\Adminer\Db;

use Lagdo\Adminer\Package;

use Exception;

global $LANG, $translations;

include __DIR__ . '/../../adminer/include/lang.inc.php';
include __DIR__ . "/../../adminer/lang/en.inc.php";

/**
 * Proxy to calls to the Adminer functions
 */
class Proxy extends Proxy\AbstractProxy
{
    use Proxy\ServerTrait;
    use Proxy\UserTrait;
    use Proxy\DatabaseTrait;
    use Proxy\TableTrait;
    use Proxy\TableSelectTrait;
    use Proxy\TableQueryTrait;
    use Proxy\ViewTrait;
    use Proxy\CommandTrait;
    use Proxy\ExportTrait;
    use Proxy\ImportTrait;

    /**
     * The breadcrumbs items
     *
     * @var array
     */
    protected $breadcrumbs = [];

    /**
     * The Jaxon Adminer package
     *
     * @var Package
     */
    protected $package;

    /**
     * The constructor
     *
     * @param Package $package    The Adminer package
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
    }

    /**
     * Get the breadcrumbs items
     *
     * @return array
     */
    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }

    /**
     * Set the breadcrumbs items
     *
     * @param array $breadcrumbs
     *
     * @return void
     */
    protected function setBreadcrumbs(array $breadcrumbs)
    {
        $this->breadcrumbs = $breadcrumbs;
    }

    /**
     * Select the database and schema
     *
     * @param string $database  The database name
     * @param string $schema    The database schema
     *
     * @return array
     */
    protected function select(string $database, string $schema)
    {
        if($database !== '')
        {
            $this->server->selectDatabase($database, $schema);
        }
    }

    /**
     * Connect to a database server
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $schema    The database schema
     *
     * @return array
     */
    protected function connect(string $server, string $database = '', string $schema = '')
    {
        $options = $this->package->getServerOptions($server);
        // Prevent multiple calls.
        if(($this->adminer))
        {
            // From adminer.inc.php
            $this->select($database, $schema);
            return $options;
        }

        $host = $options['host'];
        $port = $options['port'] ?? ''; // Optional
        $username = $options["username"];
        $password = $options["password"];
        $driver = $options['driver'];

        // Append the port to the host if it is defined.
        if(($port))
        {
            $host .= ":$port";
        }

        // The Adminer constructor connects to the database.
        $this->adminer = new Adminer([$host, $username, $password], $driver);
        $this->server = $this->adminer->server;
        $this->connection = $this->adminer->connection;
        $this->driver = $this->adminer->driver;

        // From adminer.inc.php
        $this->select($database, $schema);

        return $options;
    }

    /**
     * Check if a database server supports a given feature
     *
     * @param string $server    The selected server
     * @param string $feature   The feature to check
     *
     * @return bool
     */
    public function support(string $server, string $feature)
    {
        $this->connect($server);
        return $this->server->support($feature);
    }
}
