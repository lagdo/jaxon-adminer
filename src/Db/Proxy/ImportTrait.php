<?php

namespace Lagdo\Adminer\Db\Proxy;

use Exception;

/**
 * Proxy to calls to import functions
 */
trait ImportTrait
{
    /**
     * The proxy
     *
     * @var ImportProxy
     */
    protected $importProxy = null;

    /**
     * Get the proxy
     *
     * @param string $database      The database name
     * @param string $schema        The database schema
     *
     * @return ImportProxy
     */
    protected function import(string $database = '', string $schema = '')
    {
        if (!$this->importProxy) {
            $this->importProxy = new ImportProxy($database, $schema);
            $this->importProxy->init($this);
        }
        return $this->importProxy;
    }

    /**
     * Get data for import
     *
     * @param string $server        The selected server
     * @param string $database      The database name
     *
     * @return array
     */
    public function getImportOptions(string $server, string $database = '')
    {
        $options = $this->connect($server, $database);

        $breadcrumbs = [$options['name']];
        if (($database)) {
            $breadcrumbs[] = $database;
        }
        $breadcrumbs[] = $this->ui->lang('Import');
        $this->setBreadcrumbs($breadcrumbs);

        return $this->import()->getImportOptions($database);
    }

    /**
     * Run queries from uploaded files
     *
     * @param string $server        The selected server
     * @param array  $files         The uploaded files
     * @param bool   $errorStops    Stop executing the requests in case of error
     * @param bool   $onlyErrors    Return only errors
     * @param string $database      The database name
     * @param string $schema        The database schema
     *
     * @return array
     */
    public function executeSqlFiles(
        string $server,
        array $files,
        bool $errorStops,
        bool $onlyErrors,
        string $database = '',
        string $schema = ''
    )
    {
        $this->connect($server, $database, $schema);
        return $this->import($database, $schema)
            ->executeSqlFiles($files, $errorStops, $onlyErrors);
    }
}
