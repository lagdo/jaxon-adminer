<?php

namespace Lagdo\Adminer\Db\Proxy;

use Exception;

/**
 * Proxy to calls to table functions
 */
trait TableQueryTrait
{
    /**
     * The proxy
     *
     * @var TableProxy
     */
    protected $tableQueryProxy = null;

    /**
     * Get the proxy
     *
     * @return TableQueryProxy
     */
    protected function tableQuery()
    {
        return $this->tableQueryProxy ?: ($this->tableQueryProxy = new TableQueryProxy());
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
    public function getQueryData(string $server, string $database, string $schema,
        string $table, array $queryOptions = [], string $action = 'New item')
    {
        $options = $this->connect($server, $database, $schema);

        $this->setBreadcrumbs([$options['name'], $database,
            \adminer\lang('Tables'), $table, \adminer\lang($action)]);

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
    public function insertItem(string $server, string $database, string $schema,
        string $table, array $queryOptions)
    {
        $this->connect($server, $database, $schema);

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
    public function updateItem(string $server, string $database, string $schema,
        string $table, array $queryOptions)
    {
        $this->connect($server, $database, $schema);

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
    public function deleteItem(string $server, string $database, string $schema,
        string $table, array $queryOptions)
    {
        $this->connect($server, $database, $schema);

        return $this->tableQuery()->deleteItem($table, $queryOptions);
    }
}
