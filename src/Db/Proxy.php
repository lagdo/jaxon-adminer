<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class Proxy
{
    /**
     * The breadcrumbs items
     *
     * @var array
     */
    protected $breadcrumbs = [];

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
     * The proxy to table features
     *
     * @var TableProxy
     */
    protected $tableProxy = null;

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
     * Get the proxy to table features
     *
     * @return TableProxy
     */
    protected function table()
    {
        return $this->tableProxy ?: ($this->tableProxy = new TableProxy());
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
     * Connect to a database server
     *
     * @param array $options    The corresponding config options
     *
     * @return array
     */
    public function getServerInfo(array $options)
    {
        $this->server()->connect($options);

        // Set breadcrumbs
        $this->breadcrumbs = [$options['name']];

        return $this->server()->getServerInfo();
    }

    /**
     * Get the database list
     *
     * @param array $options    The corresponding config options
     *
     * @return void
     */
    public function getDatabases(array $options)
    {
        $this->server()->connect($options);

        // Set breadcrumbs
        $this->breadcrumbs = [$options['name'], \adminer\lang('Databases')];

        return $this->server()->getDatabases();
    }

    /**
     * Get the processes
     *
     * @param array $options    The corresponding config options
     *
     * @return array
     */
    public function getProcesses(array $options)
    {
        $this->server()->connect($options);

        // Set breadcrumbs
        $this->breadcrumbs = [$options['name'], \adminer\lang('Process list')];

        return $this->server()->getProcesses();
    }

    /**
     * Get the variables
     *
     * @param array $options    The corresponding config options
     *
     * @return array
     */
    public function getVariables(array $options)
    {
        $this->server()->connect($options);

        // Set breadcrumbs
        $this->breadcrumbs = [$options['name'], \adminer\lang('Variables')];

        return $this->server()->getVariables();
    }

    /**
     * Get the server status
     *
     * @param array $options    The corresponding config options
     *
     * @return array|null
     */
    public function getStatus(array $options)
    {
        $this->server()->connect($options);

        // Set breadcrumbs
        $this->breadcrumbs = [$options['name'], \adminer\lang('Status')];

        return $this->server()->getStatus();
    }

    /**
     * Connect to a database server
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return array
     */
    public function getDatabaseInfo(array $options, string $database)
    {
        $this->server()->connect($options, $database);

        // Set breadcrumbs
        $this->breadcrumbs = [$options['name'], $database];

        return $this->server()->getDatabaseInfo();
    }

    /**
     * Get the tables from a database server
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return array
     */
    public function getTables(array $options, string $database)
    {
        $this->server()->connect($options, $database);

        // Set breadcrumbs
        $this->breadcrumbs = [$options['name'], $database, \adminer\lang('Tables and views')];

        return $this->database()->getTables();
    }

    /**
     * Get the routines from a given database
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return array
     */
    public function getRoutines(array $options, string $database)
    {
        $this->server()->connect($options, $database);

        // Set breadcrumbs
        $this->breadcrumbs = [$options['name'], $database, \adminer\lang('Routines')];

        return $this->database()->getRoutines();
    }

    /**
     * Get the routines from a given database
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return array
     */
    public function getSequences(array $options, string $database)
    {
        $this->server()->connect($options, $database);

        // Set breadcrumbs
        $this->breadcrumbs = [$options['name'], $database, \adminer\lang('Sequences')];

        return $this->database()->getSequences();
    }

    /**
     * Get the routines from a given database
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return array
     */
    public function getUserTypes(array $options, string $database)
    {
        $this->server()->connect($options, $database);

        // Set breadcrumbs
        $this->breadcrumbs = [$options['name'], $database, \adminer\lang('User types')];

        return $this->database()->getUserTypes();
    }

    /**
     * Get the routines from a given database
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return array
     */
    public function getEvents(array $options, string $database)
    {
        $this->server()->connect($options, $database);

        // Set breadcrumbs
        $this->breadcrumbs = [$options['name'], $database, \adminer\lang('Events')];

        return $this->database()->getEvents();
    }

    /**
     * Get details about a table or a view
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     * @param string $table     The table name
     *
     * @return array
     */
    public function getTableInfo(array $options, string $database, string $table)
    {
        $this->server()->connect($options, $database);

        // Set breadcrumbs
        $this->breadcrumbs = [$options['name'], $database, $table];

        return $this->table()->getTableInfo($table);
    }

    /**
     * Get details about a table or a view
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     * @param string $table     The table name
     *
     * @return array
     */
    public function getTableFields(array $options, string $database, string $table)
    {
        $this->server()->connect($options, $database);
        return $this->table()->getTableFields($table);
    }

    /**
     * Get the indexes of a table
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     * @param string $table     The table name
     *
     * @return array|null
     */
    public function getTableIndexes(array $options, string $database, string $table)
    {
        $this->server()->connect($options, $database);
        return $this->table()->getTableIndexes($table);
    }

    /**
     * Get the foreign keys of a table
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     * @param string $table     The table name
     *
     * @return array|null
     */
    public function getTableForeignKeys(array $options, string $database, string $table)
    {
        $this->server()->connect($options, $database);
        return $this->table()->getTableForeignKeys($table);
    }

    /**
     * Get the triggers of a table
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     * @param string $table     The table name
     *
     * @return array|null
     */
    public function getTableTriggers(array $options, string $database, string $table)
    {
        $this->server()->connect($options, $database);
        return $this->table()->getTableTriggers($table);
    }
}
