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
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return array
     */
    public function getDatabaseInfo(array $options, string $database)
    {
        $this->connect($options, $database);

        $this->setBreadcrumbs([$options['name'], $database]);

        return $this->database()->getDatabaseInfo();
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
        $this->connect($options, $database);

        $this->setBreadcrumbs([$options['name'], $database, \adminer\lang('Tables and views')]);

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
        $this->connect($options, $database);

        $this->setBreadcrumbs([$options['name'], $database, \adminer\lang('Routines')]);

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
        $this->connect($options, $database);

        $this->setBreadcrumbs([$options['name'], $database, \adminer\lang('Sequences')]);

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
        $this->connect($options, $database);

        $this->setBreadcrumbs([$options['name'], $database, \adminer\lang('User types')]);

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
        $this->connect($options, $database);

        $this->setBreadcrumbs([$options['name'], $database, \adminer\lang('Events')]);

        return $this->database()->getEvents();
    }
}
