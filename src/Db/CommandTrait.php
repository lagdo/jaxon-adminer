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
     * @param string $database      The database name
     * @param string $schema        The database schema
     *
     * @return CommandProxy
     */
    protected function command(string $database, string $schema)
    {
        return $this->commandProxy ?: ($this->commandProxy = new CommandProxy($database, $schema));
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

        $labels = [
            'execute' => \adminer\lang('Execute'),
            'limit_rows' => \adminer\lang('Limit rows'),
            'error_stops' => \adminer\lang('Stop on error'),
            'only_errors' => \adminer\lang('Show only errors'),
        ];

        return ['labels' => $labels];
    }

    /**
     * Execute a query
     *
     * @param array  $options       The corresponding config options
     * @param string $query         The query to be executed
     * @param int    $limit         The max number of rows to return
     * @param bool   $errorStops    Stop executing the requests in case of error
     * @param bool   $onlyErrors    Return only errors
     * @param string $database      The database name
     * @param string $schema        The database schema
     *
     * @return array
     */
    public function executeCommands(array $options, string $query, int $limit,
        bool $errorStops, bool $onlyErrors, string $database = '', string $schema = '')
    {
        $this->connect($options, $database);
        return $this->command($database, $schema)
            ->executeCommands($query, $limit, $errorStops, $onlyErrors);
    }
}
