<?php

namespace Lagdo\Adminer\Db;

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
        return $this->exportProxy ?: ($this->exportProxy = new ExportProxy());
    }

    /**
     * Get data for export
     *
     * @param array  $options       The corresponding config options
     * @param string $database      The database name
     *
     * @return array
     */
    public function getExportOptions(array $options, string $database = '')
    {
        $this->connect($options, $database);

        $breadcrumbs = [$options['name']];
        if(($database))
        {
            $breadcrumbs[] = $database;
        }
        $breadcrumbs[] = \adminer\lang('Export');
        $this->setBreadcrumbs($breadcrumbs);

        return $this->export()->getExportOptions($database);
    }
}
