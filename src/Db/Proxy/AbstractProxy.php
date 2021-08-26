<?php

namespace Lagdo\Adminer\Db\Proxy;

use Lagdo\Adminer\Db\AdminerDb;
use Lagdo\Adminer\Db\AdminerUi;

use Lagdo\Adminer\Db\Proxy;

/**
 * Common attributes for all proxies
 */
class AbstractProxy
{
    /**
     * @var AdminerDb
     */
    public $db = null;

    /**
     * @var AdminerUi
     */
    public $ui = null;

    /**
     * Initialize the proxy
     *
     * @param Proxy $proxy
     *
     * @return void
     */
    public function init(Proxy $proxy)
    {
        $this->db = $proxy->db;
        $this->ui = $proxy->ui;
    }
}
