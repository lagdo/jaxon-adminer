<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to table functions
 */
trait TableTrait
{
    /**
     * The proxy
     *
     * @var TableProxy
     */
    protected $tableProxy = null;

    /**
     * Get the proxy
     *
     * @return TableProxy
     */
    protected function table()
    {
        return $this->tableProxy ?: ($this->tableProxy = new TableProxy());
    }

    /**
     * Get details about a table or a view
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $table     The table name
     *
     * @return array
     */
    public function getTableInfo(string $server, string $database, string $table)
    {
        $options = $this->connect($server, $database);

        $this->setBreadcrumbs([$options['name'], $database, \adminer\lang('Tables'), $table]);

        return $this->table()->getTableInfo($table);
    }

    /**
     * Get details about a table or a view
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $table     The table name
     *
     * @return array
     */
    public function getTableFields(string $server, string $database, string $table)
    {
        $this->connect($server, $database);
        return $this->table()->getTableFields($table);
    }

    /**
     * Get the indexes of a table
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $table     The table name
     *
     * @return array|null
     */
    public function getTableIndexes(string $server, string $database, string $table)
    {
        $this->connect($server, $database);
        return $this->table()->getTableIndexes($table);
    }

    /**
     * Get the foreign keys of a table
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $table     The table name
     *
     * @return array|null
     */
    public function getTableForeignKeys(string $server, string $database, string $table)
    {
        $this->connect($server, $database);
        return $this->table()->getTableForeignKeys($table);
    }

    /**
     * Get the triggers of a table
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $table     The table name
     *
     * @return array|null
     */
    public function getTableTriggers(string $server, string $database, string $table)
    {
        $this->connect($server, $database);
        return $this->table()->getTableTriggers($table);
    }
}
