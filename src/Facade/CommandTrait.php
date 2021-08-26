<?php

namespace Lagdo\Adminer\Facade;

use Exception;

/**
 * Facade to calls to command functions
 */
trait CommandTrait
{
    /**
     * The proxy
     *
     * @var CommandFacade
     */
    protected $commandFacade = null;

    /**
     * Get the proxy
     *
     * @param string $database      The database name
     * @param string $schema        The database schema
     *
     * @return CommandFacade
     */
    protected function command(string $database, string $schema)
    {
        if (!$this->commandFacade) {
            $this->commandFacade = new CommandFacade();
            $this->commandFacade->init($this);
            $this->commandFacade->connect($database, $schema);
        }
        return $this->commandFacade;
    }

    /**
     * Prepare a query
     *
     * @param string $server        The selected server
     * @param string $database      The database name
     * @param string $schema        The database schema
     *
     * @return array
     */
    public function prepareCommand(string $server, string $database = '', string $schema = '')
    {
        $options = $this->connect($server, $database, $schema);

        $breadcrumbs = [$options['name']];
        if (($database)) {
            $breadcrumbs[] = $database;
        }
        $breadcrumbs[] = $this->util->lang('SQL command');
        $this->setBreadcrumbs($breadcrumbs);

        $labels = [
            'execute' => $this->util->lang('Execute'),
            'limit_rows' => $this->util->lang('Limit rows'),
            'error_stops' => $this->util->lang('Stop on error'),
            'only_errors' => $this->util->lang('Show only errors'),
        ];

        return ['labels' => $labels];
    }

    /**
     * Execute a query
     *
     * @param string $server        The selected server
     * @param string $query         The query to be executed
     * @param int    $limit         The max number of rows to return
     * @param bool   $errorStops    Stop executing the requests in case of error
     * @param bool   $onlyErrors    Return only errors
     * @param string $database      The database name
     * @param string $schema        The database schema
     *
     * @return array
     */
    public function executeCommands(
        string $server,
        string $query,
        int $limit,
        bool $errorStops,
        bool $onlyErrors,
        string $database = '',
        string $schema = ''
    )
    {
        $this->connect($server, $database, $schema);
        return $this->command($database, $schema)
            ->executeCommands($query, $limit, $errorStops, $onlyErrors);
    }
}
