<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to command functions
 */
trait CommandTrait
{
    /**
     * The proxy
     *
     * @var CommandProxy
     */
    protected $commandProxy = null;

    /**
     * Get the proxy
     *
     * @return CommandProxy
     */
    protected function command()
    {
        return $this->commandProxy ?: ($this->commandProxy = new CommandProxy());
    }

    /**
     * Prepare a query
     *
     * @param array  $options       The corresponding config options
     * @param string $database      The database name
     *
     * @return array
     */
    public function prepareCommand(array $options, string $database = '')
    {
        $this->connect($options, $database);

        $breadcrumbs = [$options['name']];
        if(($database))
        {
            $breadcrumbs[] = $database;
        }
        $breadcrumbs[] = \adminer\lang('SQL command');
        $this->setBreadcrumbs($breadcrumbs);

        return [];
    }

    /**
     * Execute a query
     *
     * @param array  $options       The corresponding config options
     * @param string $database      The database name
     * @param string $schema        The database schema
     * @param string $query         The query to be executed
     * @param int    $limit         The max number of rows to return
     * @param bool   $errorStops    Stop executing the requests in case of error
     * @param bool   $onlyErrors    Return only errors
     *
     * @return array
     */
    public function executeCommand(array $options, string $database, string $schema,
        string $query, int $limit, bool $errorStops, bool $onlyErrors)
    {
        $this->connect($options, $database);
        return $this->command()->executeCommand($query, $limit,
            $errorStops, $onlyErrors, $database, $schema);
    }
}
