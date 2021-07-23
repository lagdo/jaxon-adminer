<?php

namespace Lagdo\Adminer\Db;

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
    public function getQueryData(string $server, string $database, string $schema,
        string $table, array $queryOptions = [])
    {
        $options = $this->connect($server, $database, $schema);

        $this->setBreadcrumbs([$options['name'], $database,
            \adminer\lang('Tables'), $table, \adminer\lang('New item')]);

        return $this->tableQuery()->getQueryData($table, $queryOptions);
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
    public function execQuery(string $server, string $database, string $schema,
        string $table, array $queryOptions = [])
    {
        $this->connect($server, $database, $schema);

        return $this->tableQuery()->execQuery($table, $queryOptions);
    }
}
