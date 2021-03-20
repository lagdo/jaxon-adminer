<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class Proxy
{
    /**
     * The proxy to server features
     *
     * @var ServerProxy
     */
    protected $serverProxy = null;

    /**
     * The proxy to database features
     *
     * @var DatabaseProxy
     */
    protected $databaseProxy = null;

    /**
     * Get the proxy to server features
     *
     * @return ServerProxy
     */
    protected function server()
    {
        return $this->serverProxy ?: ($this->serverProxy = new ServerProxy());
    }

    /**
     * Get the proxy to database features
     *
     * @return DatabaseProxy
     */
    protected function database()
    {
        return $this->databaseProxy ?: ($this->databaseProxy = new DatabaseProxy());
    }

    /**
     * Connect to a database server
     *
     * @param array $options    The corresponding config options
     *
     * @return void
     */
    public function getServerInfo(array $options)
    {
        $this->server()->connect($options);
        return $this->server()->getServerInfo($options);
    }

    /**
     * Connect to a database server
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return void
     */
    public function getDatabaseInfo(array $options, string $database)
    {
        $this->server()->connect($options, $database);
        return $this->server()->getDatabaseInfo($options, $database);
    }

    /**
     * Get the tables from a database server
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return void
     */
    public function getTables(array $options, string $database)
    {
        $this->server()->connect($options, $database);
        return $this->database()->getTables($options, $database);
    }

    /**
     * Get the routines from a given database
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return void
     */
    public function getRoutines(array $options, string $database)
    {
        $this->server()->connect($options, $database);
        return $this->database()->getRoutines($options, $database);
    }

    /**
     * Get the routines from a given database
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return void
     */
    public function getSequences(array $options, string $database)
    {
        $this->server()->connect($options, $database);
        return $this->database()->getSequences($options, $database);
    }

    /**
     * Get the routines from a given database
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return void
     */
    public function getUserTypes(array $options, string $database)
    {
        $this->server()->connect($options, $database);
        return $this->database()->getUserTypes($options, $database);
    }

    /**
     * Get the routines from a given database
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return void
     */
    public function getEvents(array $options, string $database)
    {
        $this->server()->connect($options, $database);
        return $this->database()->getEvents($options, $database);
    }
}
