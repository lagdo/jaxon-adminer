<?php

namespace Lagdo\Adminer\Db\Proxy;

use Lagdo\Adminer\Drivers\AdminerInterface;
use Lagdo\Adminer\Drivers\ServerInterface;
use Lagdo\Adminer\Drivers\DriverInterface;
use Lagdo\Adminer\Drivers\ConnectionInterface;

use Lagdo\Adminer\Db\Proxy;

/**
 * Common attributes for all proxies
 */
class AbstractProxy
{
    /**
     * @var AdminerInterface
     */
    public $adminer = null;

    /**
     * @var ServerInterface
     */
    public $server = null;

    /**
     * @var DriverInterface
     */
    public $driver = null;

    /**
     * @var ConnectionInterface
     */
    public $connection = null;

    /**
     * Initialize the proxy
     *
     * @param Proxy $proxy
     *
     * @return void
     */
    public function init(Proxy $proxy)
    {
        $this->adminer = $proxy->adminer;
        $this->server = $proxy->server;
        $this->driver = $proxy->driver;
        $this->connection = $proxy->connection;
    }
}
