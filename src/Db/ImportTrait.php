<?php

namespace Lagdo\Adminer\Db;

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
     * @return ImportProxy
     */
    protected function import()
    {
        return $this->importProxy ?: ($this->importProxy = new ImportProxy());
    }

    /**
     * Get data for import
     *
     * @param array  $options       The corresponding config options
     * @param string $database      The database name
     *
     * @return array
     */
    public function getImportOptions(array $options, string $database = '')
    {
        $this->connect($options, $database);

        $breadcrumbs = [$options['name']];
        if(($database))
        {
            $breadcrumbs[] = $database;
        }
        $breadcrumbs[] = \adminer\lang('Import');
        $this->setBreadcrumbs($breadcrumbs);

        return $this->import()->getImportOptions($database);
    }
}
