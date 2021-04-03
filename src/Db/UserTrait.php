<?php

namespace Lagdo\Adminer\Db;

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
        return $this->userProxy ?: ($this->userProxy = new UserProxy());
    }

    /**
     * Get the privilege list
     * This feature is available only for MySQL
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return array
     */
    public function getPrivileges(array $options, $database = '')
    {
        $this->connect($options);

        $this->setBreadcrumbs([$options['name'], \adminer\lang('Privileges')]);

        return $this->user()->getPrivileges($database);
    }

    /**
     * Get the privileges for a new user
     *
     * @param array $options    The corresponding config options
     *
     * @return array
     */
    public function newUserPrivileges(array $options)
    {
        $this->connect($options);
        return $this->user()->newUserPrivileges();
    }

    /**
     * Get the privileges for a new user
     *
     * @param array $options    The corresponding config options
     * @param string $user      The user name
     * @param string $host      The host name
     * @param string $database  The database name
     *
     * @return array
     */
    public function getUserPrivileges(array $options, $user, $host, $database)
    {
        $this->connect($options);
        return $this->user()->getUserPrivileges($user, $host, $database);
    }
}
