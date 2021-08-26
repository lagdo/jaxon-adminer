<?php

namespace Lagdo\Adminer\Facade;

use Exception;

/**
 * Facade to calls to user functions
 */
trait UserTrait
{
    /**
     * The proxy
     *
     * @var UserFacade
     */
    protected $userFacade = null;

    /**
     * Get the proxy to user features
     *
     * @return UserFacade
     */
    protected function user()
    {
        if (!$this->userFacade) {
            $this->userFacade = new UserFacade();
            $this->userFacade->init($this);
        }
        return $this->userFacade;
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

        $this->setBreadcrumbs([$options['name'], $this->util->lang('Privileges')]);

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
