<?php

namespace Lagdo\Adminer\Ajax;

use Lagdo\Adminer\Package;
use Lagdo\Adminer\Db\Proxy as DbProxy;

use Jaxon\CallableClass;
use Exception;

/**
 * Adminer Ajax client
 */
class Database extends CallableClass
{
    /**
     * The Jaxon Adminer package
     *
     * @var Package
     */
    protected $package;

    /**
     * The proxy to Adminer functions
     *
     * @var DbProxy
     */
    protected $dbProxy;

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
     * Show the tables of a given database
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function showTables($server, $database)
    {
        $options = $this->package->getServerOptions($server);

        $tablesInfo = $this->dbProxy->getTables($options, $database);
        // Make tables info available to views
        foreach($tablesInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        $content = $this->view()->render('adminer::views::main/content');
        $this->response->html($this->package->getDbContentId(), $content);

        return $this->response;
    }

    /**
     * Show the routines of a given database
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function showRoutines($server, $database)
    {
        $options = $this->package->getServerOptions($server);

        $routinesInfo = $this->dbProxy->getRoutines($options, $database);
        // Make routines info available to views
        foreach($routinesInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        $content = $this->view()->render('adminer::views::main/content');
        $this->response->html($this->package->getDbContentId(), $content);

        return $this->response;
    }

    /**
     * Show the sequences of a given database
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function showSequences($server, $database)
    {
        $options = $this->package->getServerOptions($server);

        $sequencesInfo = $this->dbProxy->getSequences($options, $database);
        // Make sequences info available to views
        foreach($sequencesInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        $content = $this->view()->render('adminer::views::main/content');
        $this->response->html($this->package->getDbContentId(), $content);

        return $this->response;
    }

    /**
     * Show the user types of a given database
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function showUserTypes($server, $database)
    {
        $options = $this->package->getServerOptions($server);

        $userTypesInfo = $this->dbProxy->getUserTypes($options, $database);
        // Make userTypes info available to views
        foreach($userTypesInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        $content = $this->view()->render('adminer::views::main/content');
        $this->response->html($this->package->getDbContentId(), $content);

        return $this->response;
    }

    /**
     * Show the events of a given database
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function showEvents($server, $database)
    {
        $options = $this->package->getServerOptions($server);

        $eventsInfo = $this->dbProxy->getEvents($options, $database);
        // Make events info available to views
        foreach($eventsInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        $content = $this->view()->render('adminer::views::main/content');
        $this->response->html($this->package->getDbContentId(), $content);

        return $this->response;
    }
}
