<?php

namespace Lagdo\Adminer\DbAdmin;

use Lagdo\Adminer\Db\Db;
use Lagdo\Adminer\Db\Util;

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
