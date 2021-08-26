<?php

namespace Lagdo\Adminer\Facade;

use Exception;

/**
 * Facade to calls to table functions
 */
trait TableSelectTrait
{
    /**
     * The proxy
     *
     * @var TableFacade
     */
    protected $tableSelectFacade = null;

    /**
     * Get the proxy
     *
     * @return TableSelectFacade
     */
    protected function tableSelect()
    {
        if (!$this->tableSelectFacade) {
            $this->tableSelectFacade = new TableSelectFacade();
            $this->tableSelectFacade->init($this);
        }
        return $this->tableSelectFacade;
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
            $this->util->lang('Tables'), $table, $this->util->lang('Select')]);

        $this->util->input->table = $table;
        $this->util->input->values = $queryOptions;
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

        $this->util->input->table = $table;
        $this->util->input->values = $queryOptions;
        return $this->tableSelect()->execSelect($table, $queryOptions);
    }
}
