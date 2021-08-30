<?php

namespace Lagdo\Adminer\DbAdmin;

use Exception;

/**
 * Admin database functions
 */
trait DatabaseTrait
{
    /**
     * The proxy
     *
     * @var DatabaseAdmin
     */
    protected $databaseAdmin = null;

    /**
     * Get the proxy
     *
     * @param array $options    The server config options
     *
     * @return DatabaseAdmin
     */
    protected function database(array $options)
    {
        if (!$this->databaseAdmin) {
            $this->databaseAdmin = new DatabaseAdmin($options);
            $this->databaseAdmin->init($this);
        }
        return $this->databaseAdmin;
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

        $this->setBreadcrumbs([$options['name'], $database, $this->util->lang('Tables')]);

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

        $this->setBreadcrumbs([$options['name'], $database, $this->util->lang('Views')]);

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

        $this->setBreadcrumbs([$options['name'], $database, $this->util->lang('Routines')]);

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

        $this->setBreadcrumbs([$options['name'], $database, $this->util->lang('Sequences')]);

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

        $this->setBreadcrumbs([$options['name'], $database, $this->util->lang('User types')]);

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

        $this->setBreadcrumbs([$options['name'], $database, $this->util->lang('Events')]);

        return $this->database($options)->getEvents();
    }
}
