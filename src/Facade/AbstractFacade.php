<?php

namespace Lagdo\Adminer\Facade;

use Lagdo\Adminer\Db\AdminerDb;
use Lagdo\Adminer\Db\AdminerUtil;

use Lagdo\Adminer\DbAdmin;

/**
 * Common attributes for all proxies
 */
class AbstractFacade
{
    /**
     * @var AdminerDb
     */
    public $db = null;

    /**
     * @var AdminerUtil
     */
    public $util = null;

    /**
     * Initialize the facade
     *
     * @param DbAdmin $dbAdmin
     *
     * @return void
     */
    public function init(DbAdmin $dbAdmin)
    {
        $this->db = $dbAdmin->db;
        $this->util = $dbAdmin->util;
    }
}
