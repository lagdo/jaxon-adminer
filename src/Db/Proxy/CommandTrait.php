<?php

namespace Lagdo\Adminer\Db\Proxy;

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
        if (!$this->commandProxy) {
            $this->commandProxy = new CommandProxy();
            $this->commandProxy->init($this);
            $this->commandProxy->connect($database, $schema);
        }
        return $this->commandProxy;
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
        $breadcrumbs[] = $this->ui->lang('SQL command');
        $this->setBreadcrumbs($breadcrumbs);

        $labels = [
            'execute' => $this->ui->lang('Execute'),
            'limit_rows' => $this->ui->lang('Limit rows'),
            'error_stops' => $this->ui->lang('Stop on error'),
            'only_errors' => $this->ui->lang('Show only errors'),
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
