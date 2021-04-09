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
     * @return DatabaseProxy
     */
    protected function database()
    {
        return $this->databaseProxy ?: ($this->databaseProxy = new DatabaseProxy());
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

        return $this->database()->getDatabaseInfo();
    }

    /**
     * Get the tables from a database server
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     *
     * @return array
     */
    public function getTables(string $server, string $database)
    {
        $options = $this->connect($server, $database);

        $this->setBreadcrumbs([$options['name'], $database, \adminer\lang('Tables and views')]);

        return $this->database()->getTables();
    }

    /**
     * Get the routines from a given database
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     *
     * @return array
     */
    public function getRoutines(string $server, string $database)
    {
        $options = $this->connect($server, $database);

        $this->setBreadcrumbs([$options['name'], $database, \adminer\lang('Routines')]);

        return $this->database()->getRoutines();
    }

    /**
     * Get the routines from a given database
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     *
     * @return array
     */
    public function getSequences(string $server, string $database)
    {
        $options = $this->connect($server, $database);

        $this->setBreadcrumbs([$options['name'], $database, \adminer\lang('Sequences')]);

        return $this->database()->getSequences();
    }

    /**
     * Get the routines from a given database
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     *
     * @return array
     */
    public function getUserTypes(string $server, string $database)
    {
        $options = $this->connect($server, $database);

        $this->setBreadcrumbs([$options['name'], $database, \adminer\lang('User types')]);

        return $this->database()->getUserTypes();
    }

    /**
     * Get the routines from a given database
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     *
     * @return array
     */
    public function getEvents(string $server, string $database)
    {
        $options = $this->connect($server, $database);

        $this->setBreadcrumbs([$options['name'], $database, \adminer\lang('Events')]);

        return $this->database()->getEvents();
    }
}
