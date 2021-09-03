<?php

namespace Lagdo\DbAdmin\DbAdmin;

use Lagdo\DbAdmin\Db\Db;
use Lagdo\DbAdmin\Db\Util;

/**
 * Common attributes for all admins
 */
class AbstractAdmin
{
    /**
     * @var Db
     */
    public $db = null;

    /**
     * @var Util
     */
    public $util = null;

    /**
     * Initialize the facade
     *
     * @param AbstractAdmin $dbAdmin
     *
     * @return void
     */
    public function init(AbstractAdmin $dbAdmin)
    {
        $this->db = $dbAdmin->db;
        $this->util = $dbAdmin->util;
    }
}
