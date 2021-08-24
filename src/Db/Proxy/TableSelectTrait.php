<?php

namespace Lagdo\Adminer\Db\Proxy;

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
        if (!$this->tableSelectProxy) {
            $this->tableSelectProxy = new TableSelectProxy();
            $this->tableSelectProxy->init($this);
        }
        return $this->tableSelectProxy;
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
    public function getSelectData(
        string $server,
        string $database,
        string $schema,
        string $table,
        array $queryOptions = []
    )
    {
        $options = $this->connect($server, $database, $schema);

        $this->setBreadcrumbs([$options['name'], $database,
            $this->ui->lang('Tables'), $table, $this->ui->lang('Select')]);

        $this->ui->input->table = $table;
        $this->ui->input->values = $queryOptions;
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
    public function execSelect(
        string $server,
        string $database,
        string $schema,
        string $table,
        array $queryOptions = []
    )
    {
        $this->connect($server, $database, $schema);

        $this->ui->input->table = $table;
        $this->ui->input->values = $queryOptions;
        return $this->tableSelect()->execSelect($table, $queryOptions);
    }
}
