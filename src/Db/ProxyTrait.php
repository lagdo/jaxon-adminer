<?php

namespace Lagdo\Adminer\Db;

use Lagdo\Adminer\Drivers\AdminerInterface;
use Lagdo\Adminer\Drivers\ServerInterface;
use Lagdo\Adminer\Drivers\DriverInterface;
use Lagdo\Adminer\Drivers\ConnectionInterface;

/**
 * Proxy to calls to the Adminer functions
 */
trait ProxyTrait
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
     * Initialise the proxy
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
