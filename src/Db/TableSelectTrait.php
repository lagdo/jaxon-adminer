<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to table functions
 */
trait TableSelectTrait
{
    /**
     * The proxy
     *
     * @var TableProxy
     */
    protected $tableSelectProxy = null;

    /**
     * Get the proxy
     *
     * @return TableSelectProxy
     */
    protected function tableSelect()
    {
        return $this->tableSelectProxy ?: ($this->tableSelectProxy = new TableSelectProxy());
    }

    /**
     * Get required data for create/update on tables
     *
     * @param string $server        The selected server
     * @param string $database      The database name
     * @param string $schema        The database schema
     * @param string $table         The table name
     * @param array  $queryOptions  The query options
     *
     * @return array
     */
    public function getSelectData(string $server, string $database, string $schema,
        string $table, array $queryOptions = [])
    {
        $options = $this->connect($server, $database, $schema);

        $this->setBreadcrumbs([$options['name'], $database,
            \adminer\lang('Tables'), $table, \adminer\lang('Select')]);

        return $this->tableSelect()->getSelectData($table, $queryOptions);
    }

    /**
     * Get required data for create/update on tables
     *
     * @param string $server        The selected server
     * @param string $database      The database name
     * @param string $schema        The database schema
     * @param string $table         The table name
     * @param array  $queryOptions  The query options
     *
     * @return array
     */
    public function execSelect(string $server, string $database, string $schema,
        string $table, array $queryOptions = [])
    {
        $this->connect($server, $database, $schema);

        return $this->tableSelect()->execSelect($table, $queryOptions);
    }
}
