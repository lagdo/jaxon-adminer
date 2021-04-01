<?php

namespace Lagdo\Adminer\Ajax;

use Lagdo\Adminer\Package;
use Lagdo\Adminer\AdminerCallable;
use Lagdo\Adminer\Db\Proxy as DbProxy;

use Exception;

/**
 * Adminer Ajax
 */
class User extends AdminerCallable
{
    /**
     * The constructor
     *
     * @param Package $package    The Adminer package
     * @param DbProxy $dbProxy    The proxy to Adminer
     */
    public function __construct(Package $package, DbProxy $dbProxy)
    {
        $this->package = $package;
        $this->dbProxy = $dbProxy;
    }

    /**
     * Show the new user form
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function add(string $server, string $database = '')
    {
        $options = $this->package->getServerOptions($server);

        $userInfo = $this->dbProxy->newUserPrivileges($options, $database);

        // Make user info available to views
        foreach($userInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        $content = $this->render('main/content');
        $this->response->html($this->package->getDbContentId(), $content);

        return $this->response;
    }

    /**
     * Show the edit user form
     *
     * @param string $server    The database server
     * @param string $username  The user name
     * @param string $hostname  The host name
     * @param string $database  The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function edit(string $server, string $username, string $hostname, string $database = '')
    {
        $options = $this->package->getServerOptions($server);

        $userInfo = $this->dbProxy->getUserPrivileges($options, $database, $username, $hostname);

        // Make user info available to views
        foreach($userInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        $content = $this->render('main/content');
        $this->response->html($this->package->getDbContentId(), $content);

        return $this->response;
    }
}
