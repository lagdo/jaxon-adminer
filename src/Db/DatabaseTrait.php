<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to database functions
 */
trait DatabaseTrait
{
    /**
     * The proxy
     *
     * @var DatabaseProxy
     */
    protected $databaseProxy = null;

    /**
     * Get the proxy
     *
     * @param array $options    The server config options
     *
     * @return DatabaseProxy
     */
    protected function database(array $options)
    {
        if(!$this->databaseProxy)
        {
            $this->databaseProxy = new DatabaseProxy($options);
            $this->databaseProxy->init($this);
        }
        return $this->databaseProxy;
    }

    /**
     * Connect to a database server
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     *
     * @return array
     */
    public function getDatabaseInfo(string $server, string $database)
    {
        $options = $this->connect($server, $database);

        $this->setBreadcrumbs([$options['name'], $database]);

        return $this->database($options)->getDatabaseInfo();
    }

    /**
     * Get the tables from a database server
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $schema    The database schema
     *
     * @return array
     */
    public function getTables(string $server, string $database, string $schema)
    {
        $options = $this->connect($server, $database, $schema);

        $this->setBreadcrumbs([$options['name'], $database, $this->adminer->lang('Tables')]);

        return $this->database($options)->getTables();
    }

    /**
     * Get the views from a database server
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $schema    The database schema
     *
     * @return array
     */
    public function getViews(string $server, string $database, string $schema)
    {
        $options = $this->connect($server, $database, $schema);

        $this->setBreadcrumbs([$options['name'], $database, $this->adminer->lang('Views')]);

        return $this->database($options)->getViews();
    }

    /**
     * Get the routines from a given database
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $schema    The database schema
     *
     * @return array
     */
    public function getRoutines(string $server, string $database, string $schema)
    {
        $options = $this->connect($server, $database, $schema);

        $this->setBreadcrumbs([$options['name'], $database, $this->adminer->lang('Routines')]);

        return $this->database($options)->getRoutines();
    }

    /**
     * Get the sequences from a given database
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $schema    The database schema
     *
     * @return array
     */
    public function getSequences(string $server, string $database, string $schema)
    {
        $options = $this->connect($server, $database, $schema);

        $this->setBreadcrumbs([$options['name'], $database, $this->adminer->lang('Sequences')]);

        return $this->database($options)->getSequences();
    }

    /**
     * Get the user types from a given database
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $schema    The database schema
     *
     * @return array
     */
    public function getUserTypes(string $server, string $database, string $schema)
    {
        $options = $this->connect($server, $database, $schema);

        $this->setBreadcrumbs([$options['name'], $database, $this->adminer->lang('User types')]);

        return $this->database($options)->getUserTypes();
    }

    /**
     * Get the events from a given database
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $schema    The database schema
     *
     * @return array
     */
    public function getEvents(string $server, string $database, string $schema)
    {
        $options = $this->connect($server, $database, $schema);

        $this->setBreadcrumbs([$options['name'], $database, $this->adminer->lang('Events')]);

        return $this->database($options)->getEvents();
    }
}
