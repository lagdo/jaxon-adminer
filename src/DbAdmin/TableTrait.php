<?php

namespace Lagdo\DbAdmin\DbAdmin;

use Exception;

/**
 * Admin table functions
 */
trait TableTrait
{
    /**
     * The proxy
     *
     * @var TableAdmin
     */
    protected $tableAdmin = null;

    /**
     * Get the proxy
     *
     * @return TableAdmin
     */
    protected function table()
    {
        if (!$this->tableAdmin) {
            $this->tableAdmin = new TableAdmin();
            $this->tableAdmin->init($this);
        }
        return $this->tableAdmin;
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
        $this->connect($server, $database, $schema);

        $options = $this->package->getServerOptions($server);
        $this->setBreadcrumbs([$options['name'], $database, $this->util->lang('Tables'), $table]);

        $this->util->input->table = $table;
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
        $this->util->input->table = $table;
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
        $this->util->input->table = $table;
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
        $this->util->input->table = $table;
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
        $this->util->input->table = $table;
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
        $this->connect($server, $database, $schema);

        $options = $this->package->getServerOptions($server);
        $breadcrumbs = [$options['name'], $database, $this->util->lang('Tables')];
        if (($table)) {
            $breadcrumbs[] = $table;
            $breadcrumbs[] = $this->util->lang('Alter table');
        } else {
            $breadcrumbs[] = $this->util->lang('Create table');
        }
        $this->setBreadcrumbs($breadcrumbs);

        $this->util->input->table = $table;
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
        $this->connect($server, $database, $schema);

        $options = $this->package->getServerOptions($server);
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
        $this->util->input->table = $table;
        $this->util->input->values = $values;
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
        $this->util->input->table = $table;
        $this->util->input->values = $values;
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
        $this->util->input->table = $table;
        return $this->table()->dropTable($table);
    }
}
