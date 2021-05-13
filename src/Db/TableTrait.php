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

    /**
     * Get required data for create/update on tables
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $table     The table name
     *
     * @return array
     */
    public function getTableData(string $server, string $database, string $table = '')
    {
        $options = $this->connect($server, $database);

        $breadcrumbs = [$options['name'], $database, \adminer\lang('Tables')];
        if(($table))
        {
            $breadcrumbs[] = $table;
            $breadcrumbs[] = \adminer\lang('Alter table');
        }
        else
        {
            $breadcrumbs[] = \adminer\lang('Create table');
        }
        $this->setBreadcrumbs($breadcrumbs);

        return $this->table()->getTableData($table);
    }

    /**
     * Get fields for a new column
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     *
     * @return array
     */
    public function getTableField(string $server, string $database)
    {
        $options = $this->connect($server, $database);
        return $this->table()->getTableField();
    }

    /**
     * Create a table
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param array  $values    The table values
     *
     * @return array|null
     */
    public function createTable(string $server, string $database, array $values)
    {
        $this->connect($server, $database);
        return $this->table()->createTable($values);
    }

    /**
     * Alter a table
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $table     The table name
     * @param array  $values    The table values
     *
     * @return array|null
     */
    public function alterTable(string $server, string $database, string $table, array $values)
    {
        $this->connect($server, $database);
        return $this->table()->alterTable($table, $values);
    }

    /**
     * Drop a table
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $table     The table name
     *
     * @return array|null
     */
    public function dropTable(string $server, string $database, string $table)
    {
        $this->connect($server, $database);
        return $this->table()->dropTable($table);
    }
}
