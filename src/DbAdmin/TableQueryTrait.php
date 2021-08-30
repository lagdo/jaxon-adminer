<?php

namespace Lagdo\Adminer\DbAdmin;

use Exception;

/**
 * Admin table query functions
 */
trait TableQueryTrait
{
    /**
     * The proxy
     *
     * @var TableAdmin
     */
    protected $tableQueryAdmin = null;

    /**
     * Get the proxy
     *
     * @return TableQueryAdmin
     */
    protected function tableQuery()
    {
        if (!$this->tableQueryAdmin) {
            $this->tableQueryAdmin = new TableQueryAdmin();
            $this->tableQueryAdmin->init($this);
        }
        return $this->tableQueryAdmin;
    }

    /**
     * Get data for insert/update on a table
     *
     * @param string $server        The selected server
     * @param string $database      The database name
     * @param string $schema        The database schema
     * @param string $table         The table name
     * @param array  $queryOptions  The query options
     * @param string $action        The action title
     *
     * @return array
     */
    public function getQueryData(
        string $server,
        string $database,
        string $schema,
        string $table,
        array $queryOptions = [],
        string $action = 'New item'
    )
    {
        $options = $this->connect($server, $database, $schema);

        $this->setBreadcrumbs([$options['name'], $database,
            $this->util->lang('Tables'), $table, $this->util->lang($action)]);

        $this->util->input->table = $table;
        $this->util->input->values = $queryOptions;
        return $this->tableQuery()->getQueryData($table, $queryOptions);
    }

    /**
     * Insert a new item in a table
     *
     * @param string $server        The selected server
     * @param string $database      The database name
     * @param string $schema        The database schema
     * @param string $table         The table name
     * @param array  $queryOptions  The query options
     *
     * @return array
     */
    public function insertItem(
        string $server,
        string $database,
        string $schema,
        string $table,
        array $queryOptions
    )
    {
        $this->connect($server, $database, $schema);

        $this->util->input->table = $table;
        $this->util->input->values = $queryOptions;
        return $this->tableQuery()->insertItem($table, $queryOptions);
    }

    /**
     * Update one or more items in a table
     *
     * @param string $server        The selected server
     * @param string $database      The database name
     * @param string $schema        The database schema
     * @param string $table         The table name
     * @param array  $queryOptions  The query options
     *
     * @return array
     */
    public function updateItem(
        string $server,
        string $database,
        string $schema,
        string $table,
        array $queryOptions
    )
    {
        $this->connect($server, $database, $schema);

        $this->util->input->table = $table;
        $this->util->input->values = $queryOptions;
        return $this->tableQuery()->updateItem($table, $queryOptions);
    }

    /**
     * Delete one or more items in a table
     *
     * @param string $server        The selected server
     * @param string $database      The database name
     * @param string $schema        The database schema
     * @param string $table         The table name
     * @param array  $queryOptions  The query options
     *
     * @return array
     */
    public function deleteItem(
        string $server,
        string $database,
        string $schema,
        string $table,
        array $queryOptions
    )
    {
        $this->connect($server, $database, $schema);

        $this->util->input->table = $table;
        $this->util->input->values = $queryOptions;
        return $this->tableQuery()->deleteItem($table, $queryOptions);
    }
}
