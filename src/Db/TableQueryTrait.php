<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to table functions
 */
trait TableQueryTrait
{
    /**
     * The proxy
     *
     * @var TableProxy
     */
    protected $tableQueryProxy = null;

    /**
     * Get the proxy
     *
     * @return TableQueryProxy
     */
    protected function tableQuery()
    {
        return $this->tableQueryProxy ?: ($this->tableQueryProxy = new TableQueryProxy());
    }
}
