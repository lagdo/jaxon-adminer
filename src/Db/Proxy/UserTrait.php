<?php

namespace Lagdo\Adminer\Db\Proxy;

use Exception;

/**
 * Proxy to calls to user functions
 */
trait UserTrait
{
    /**
     * The proxy
     *
     * @var UserProxy
     */
    protected $userProxy = null;

    /**
     * Get the proxy to user features
     *
     * @return UserProxy
     */
    protected function user()
    {
        if (!$this->userProxy) {
            $this->userProxy = new UserProxy();
            $this->userProxy->init($this);
        }
        return $this->userProxy;
    }

    /**
     * Get the privilege list
     * This feature is available only for MySQL
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     *
     * @return array
     */
    public function getPrivileges(string $server, string $database = '')
    {
        $options = $this->connect($server);

        $this->setBreadcrumbs([$options['name'], $this->ui->lang('Privileges')]);

        return $this->user()->getPrivileges($database);
    }

    /**
     * Get the privileges for a new user
     *
     * @param string $server    The selected server
     *
     * @return array
     */
    public function newUserPrivileges(string $server)
    {
        $this->connect($server);
        return $this->user()->newUserPrivileges();
    }

    /**
     * Get the privileges for a new user
     *
     * @param string $server    The selected server
     * @param string $user      The user name
     * @param string $host      The host name
     * @param string $database  The database name
     *
     * @return array
     */
    public function getUserPrivileges(string $server, string $user, string $host, string $database)
    {
        $this->connect($server);
        return $this->user()->getUserPrivileges($user, $host, $database);
    }
}
