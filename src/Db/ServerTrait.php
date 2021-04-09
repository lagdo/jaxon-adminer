<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to server functions
 */
trait ServerTrait
{
    /**
     * The proxy
     *
     * @var ServerProxy
     */
    protected $serverProxy = null;

    /**
     * Get the proxy
     *
     * @param array $options    The server config options
     *
     * @return ServerProxy
     */
    protected function server(array $options)
    {
        return $this->serverProxy ?: ($this->serverProxy = new ServerProxy($options));
    }

    /**
     * Connect to a database server
     *
     * @param string $server    The selected server
     *
     * @return array
     */
    public function getServerInfo(string $server)
    {
        $options = $this->connect($server);

        $this->setBreadcrumbs([$options['name']]);

        return $this->server($options)->getServerInfo();
    }

    /**
     * Get the collation list
     *
     * @param string $server    The selected server
     *
     * @return array
     */
    public function getCollations(string $server)
    {
        $options = $this->connect($server);
        return $this->server($options)->getCollations();
    }

    /**
     * Get the database list
     *
     * @param string $server    The selected server
     *
     * @return array
     */
    public function getDatabases(string $server)
    {
        $options = $this->connect($server);

        $this->setBreadcrumbs([$options['name'], \adminer\lang('Databases')]);

        return $this->server($options)->getDatabases();
    }

    /**
     * Get the processes
     *
     * @param string $server    The selected server
     *
     * @return array
     */
    public function getProcesses(string $server)
    {
        $options = $this->connect($server);

        $this->setBreadcrumbs([$options['name'], \adminer\lang('Process list')]);

        return $this->server($options)->getProcesses();
    }

    /**
     * Get the variables
     *
     * @param string $server    The selected server
     *
     * @return array
     */
    public function getVariables(string $server)
    {
        $options = $this->connect($server);

        $this->setBreadcrumbs([$options['name'], \adminer\lang('Variables')]);

        return $this->server($options)->getVariables();
    }

    /**
     * Get the server status
     *
     * @param string $server    The selected server
     *
     * @return array|null
     */
    public function getStatus(string $server)
    {
        $options = $this->connect($server);

        $this->setBreadcrumbs([$options['name'], \adminer\lang('Status')]);

        return $this->server($options)->getStatus();
    }

    /**
     * Create a database
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $collation The database collation
     *
     * @return bool
     */
    public function createDatabase(string $server, string $database, string $collation = '')
    {
        $options = $this->connect($server);
        return $this->server($options)->createDatabase($database, $collation);
    }

    /**
     * Drop a database
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     *
     * @return bool
     */
    public function dropDatabase(string $server, string $database)
    {
        $options = $this->connect($server);
        return $this->server($options)->dropDatabase($database);
    }
}
