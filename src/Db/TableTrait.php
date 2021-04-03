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
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     * @param string $table     The table name
     *
     * @return array
     */
    public function getTableInfo(array $options, string $database, string $table)
    {
        $this->connect($options, $database);

        $this->setBreadcrumbs([$options['name'], $database, $table]);

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
        $this->connect($options, $database);
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
        $this->connect($options, $database);
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
        $this->connect($options, $database);
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
        $this->connect($options, $database);
        return $this->table()->getTableTriggers($table);
    }
}
