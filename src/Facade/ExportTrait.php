<?php

namespace Lagdo\Adminer\Facade;

use Exception;

/**
 * Facade to calls to export functions
 */
trait ExportTrait
{
    /**
     * The proxy
     *
     * @var ExportFacade
     */
    protected $exportFacade = null;

    /**
     * Get the proxy
     *
     * @return ExportFacade
     */
    protected function export()
    {
        if (!$this->exportFacade) {
            $this->exportFacade = new ExportFacade();
            $this->exportFacade->init($this);
        }
        return $this->exportFacade;
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
        if (($database)) {
            $breadcrumbs[] = $database;
        }
        $breadcrumbs[] = $this->util->lang('Export');
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
