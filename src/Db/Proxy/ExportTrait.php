<?php

namespace Lagdo\Adminer\Db\Proxy;

use Exception;

/**
 * Proxy to calls to export functions
 */
trait ExportTrait
{
    /**
     * The proxy
     *
     * @var ExportProxy
     */
    protected $exportProxy = null;

    /**
     * Get the proxy
     *
     * @return ExportProxy
     */
    protected function export()
    {
        if(!$this->exportProxy)
        {
            $this->exportProxy = new ExportProxy();
            $this->exportProxy->init($this);
        }
        return $this->exportProxy;
    }

    /**
     * Get data for export
     *
     * @param string $server        The selected server
     * @param string $database      The database name
     *
     * @return array
     */
    public function getExportOptions(string $server, string $database = '')
    {
        $options = $this->connect($server, $database);

        $breadcrumbs = [$options['name']];
        if(($database))
        {
            $breadcrumbs[] = $database;
        }
        $breadcrumbs[] = $this->ui->lang('Export');
        $this->setBreadcrumbs($breadcrumbs);

        return $this->export()->getExportOptions($database);
    }

    /**
     * Export databases
     * The databases and tables parameters are array where the keys are names and the values
     * are boolean which indicate whether the corresponding data should be exported too.
     *
     * @param string $server        The selected server
     * @param array  $databases     The databases to dump
     * @param array  $tables        The tables to dump
     * @param array  $dumpOptions   The export options
     *
     * @return array
     */
    public function exportDatabases(string $server, array $databases, array $tables, array $dumpOptions)
    {
        $this->connect($server);
        return $this->export()->exportDatabases($databases, $tables, $dumpOptions);
    }
}
