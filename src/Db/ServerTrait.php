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
     * @param array $options    The server config options
     *
     * @return array
     */
    public function getServerInfo(array $options)
    {
        $this->connect($options);

        $this->setBreadcrumbs([$options['name']]);

        return $this->server($options)->getServerInfo();
    }

    /**
     * Get the collation list
     *
     * @param array $options    The server config options
     *
     * @return array
     */
    public function getCollations(array $options)
    {
        $this->connect($options);
        return $this->server($options)->getCollations();
    }

    /**
     * Get the database list
     *
     * @param array $options    The server config options
     *
     * @return array
     */
    public function getDatabases(array $options)
    {
        $this->connect($options);

        $this->setBreadcrumbs([$options['name'], \adminer\lang('Databases')]);

        return $this->server($options)->getDatabases();
    }

    /**
     * Get the processes
     *
     * @param array $options    The server config options
     *
     * @return array
     */
    public function getProcesses(array $options)
    {
        $this->connect($options);

        $this->setBreadcrumbs([$options['name'], \adminer\lang('Process list')]);

        return $this->server($options)->getProcesses();
    }

    /**
     * Get the variables
     *
     * @param array $options    The server config options
     *
     * @return array
     */
    public function getVariables(array $options)
    {
        $this->connect($options);

        $this->setBreadcrumbs([$options['name'], \adminer\lang('Variables')]);

        return $this->server($options)->getVariables();
    }

    /**
     * Get the server status
     *
     * @param array $options    The server config options
     *
     * @return array|null
     */
    public function getStatus(array $options)
    {
        $this->connect($options);

        $this->setBreadcrumbs([$options['name'], \adminer\lang('Status')]);

        return $this->server($options)->getStatus();
    }

    /**
     * Create a database
     *
     * @param array $options    The server config options
     * @param string $database  The database name
     * @param string $collation The database collation
     *
     * @return bool
     */
    public function createDatabase(array $options, string $database, string $collation = '')
    {
        $this->connect($options);
        return $this->server($options)->createDatabase($database, $collation);
    }

    /**
     * Drop a database
     *
     * @param array $options    The server config options
     * @param string $database  The database name
     *
     * @return bool
     */
    public function dropDatabase(array $options, string $database)
    {
        $this->connect($options);
        return $this->server($options)->dropDatabase($database);
    }
}
