<?php

namespace Lagdo\Adminer\Db\Proxy;

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
        if (!$this->tableProxy) {
            $this->tableProxy = new TableProxy();
            $this->tableProxy->init($this);
        }
        return $this->tableProxy;
    }

    /**
     * Get details about a table or a view
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $schema    The database schema
     * @param string $table     The table name
     *
     * @return array
     */
    public function getTableInfo(string $server, string $database, string $schema, string $table)
    {
        $options = $this->connect($server, $database, $schema);

        $this->setBreadcrumbs([$options['name'], $database, $this->ui->lang('Tables'), $table]);

        $this->ui->input->table = $table;
        return $this->table()->getTableInfo($table);
    }

    /**
     * Get details about a table or a view
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $schema    The database schema
     * @param string $table     The table name
     *
     * @return array
     */
    public function getTableFields(string $server, string $database, string $schema, string $table)
    {
        $this->connect($server, $database, $schema);
        $this->ui->input->table = $table;
        return $this->table()->getTableFields($table);
    }

    /**
     * Get the indexes of a table
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $schema    The database schema
     * @param string $table     The table name
     *
     * @return array|null
     */
    public function getTableIndexes(string $server, string $database, string $schema, string $table)
    {
        $this->connect($server, $database, $schema);
        $this->ui->input->table = $table;
        return $this->table()->getTableIndexes($table);
    }

    /**
     * Get the foreign keys of a table
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $schema    The database schema
     * @param string $table     The table name
     *
     * @return array|null
     */
    public function getTableForeignKeys(string $server, string $database, string $schema, string $table)
    {
        $this->connect($server, $database, $schema);
        $this->ui->input->table = $table;
        return $this->table()->getTableForeignKeys($table);
    }

    /**
     * Get the triggers of a table
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $schema    The database schema
     * @param string $table     The table name
     *
     * @return array|null
     */
    public function getTableTriggers(string $server, string $database, string $schema, string $table)
    {
        $this->connect($server, $database, $schema);
        $this->ui->input->table = $table;
        return $this->table()->getTableTriggers($table);
    }

    /**
     * Get required data for create/update on tables
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $schema    The database schema
     * @param string $table     The table name
     *
     * @return array
     */
    public function getTableData(string $server, string $database, string $schema, string $table = '')
    {
        $options = $this->connect($server, $database, $schema);

        $breadcrumbs = [$options['name'], $database, $this->ui->lang('Tables')];
        if (($table)) {
            $breadcrumbs[] = $table;
            $breadcrumbs[] = $this->ui->lang('Alter table');
        } else {
            $breadcrumbs[] = $this->ui->lang('Create table');
        }
        $this->setBreadcrumbs($breadcrumbs);

        $this->ui->input->table = $table;
        return $this->table()->getTableData($table);
    }

    /**
     * Get fields for a new column
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $schema    The database schema
     *
     * @return array
     */
    public function getTableField(string $server, string $database, string $schema)
    {
        $options = $this->connect($server, $database, $schema);
        return $this->table()->getTableField();
    }

    /**
     * Create a table
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $schema    The database schema
     * @param array  $values    The table values
     *
     * @return array|null
     */
    public function createTable(string $server, string $database, string $schema, array $values)
    {
        $this->connect($server, $database, $schema);
        $this->ui->input->table = $table;
        $this->ui->input->values = $values;
        return $this->table()->createTable($values);
    }

    /**
     * Alter a table
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $schema    The database schema
     * @param string $table     The table name
     * @param array  $values    The table values
     *
     * @return array|null
     */
    public function alterTable(string $server, string $database, string $schema, string $table, array $values)
    {
        $this->connect($server, $database, $schema);
        $this->ui->input->table = $table;
        $this->ui->input->values = $values;
        return $this->table()->alterTable($table, $values);
    }

    /**
     * Drop a table
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $schema    The database schema
     * @param string $table     The table name
     *
     * @return array|null
     */
    public function dropTable(string $server, string $database, string $schema, string $table)
    {
        $this->connect($server, $database, $schema);
        $this->ui->input->table = $table;
        return $this->table()->dropTable($table);
    }
}
