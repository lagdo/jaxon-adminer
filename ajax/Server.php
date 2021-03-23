<?php

namespace Lagdo\Adminer\Ajax;

use Lagdo\Adminer\Package;
use Lagdo\Adminer\AdminerCallable;
use Lagdo\Adminer\Db\Proxy as DbProxy;

use Exception;

/**
 * Adminer Ajax
 */
class Server extends AdminerCallable
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
     * Connect to a db server.
     * The database list will be displayed in the HTML select component.
     *
     * @param string $server      The database server
     *
     * @return \Jaxon\Response\Response
     */
    public function connect($server)
    {
        $options = $this->package->getServerOptions($server);

        $serverInfo = $this->dbProxy->getServerInfo($options);
        // Make server info available to views
        foreach($serverInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        $content = $this->render('menu/server');
        $this->response->html($this->package->getDbListId(), $content);
        $this->jq('#adminer-dbname-select-btn')
            ->click($this->rq()->select($server, \pm()->select('adminer-dbname-select')));

        $content = $this->render('menu/actions');
        $this->response->html($this->package->getServerActionsId(), $content);
        $this->response->clear($this->package->getDbActionsId());
        $this->response->clear($this->package->getDbMenuId());

        $content = $this->render('main/server');
        $this->response->html($this->package->getDbContentId(), $content);

        // Set the click handlers
        // $this->jq('#adminer-main-action-database')
        //     ->removeClass('btn-default')
        //     ->addClass('btn-primary')
        //     ->click($this->rq()->createDatabase($server));
        // $this->jq('#adminer-main-action-privileges')
        //     ->removeClass('btn-default')
        //     ->addClass('btn-primary')
        //     ->click($this->rq()->showPrivileges($server));
        $this->jq('#adminer-main-action-processlist')
            ->removeClass('btn-default')
            ->addClass('btn-primary')
            ->click($this->rq()->showProcesses($server));
        $this->jq('#adminer-main-action-variables')
            ->removeClass('btn-default')
            ->addClass('btn-primary')
            ->click($this->rq()->showVariables($server));
        $this->jq('#adminer-main-action-status')
            ->removeClass('btn-default')
            ->addClass('btn-primary')
            ->click($this->rq()->showStatus($server));

        return $this->response;
    }

    /**
     * Show the processes of a server
     *
     * @param string $server      The database server
     *
     * @return \Jaxon\Response\Response
     */
    public function showProcesses($server)
    {
        $options = $this->package->getServerOptions($server);

        $processesInfo = $this->dbProxy->getProcesses($options);
        // Make processes info available to views
        foreach($processesInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        $content = $this->render('main/row-content');
        $this->response->html('adminer-server-main-table', $content);

        return $this->response;
    }

    /**
     * Show the variables of a server
     *
     * @param string $server      The database server
     *
     * @return \Jaxon\Response\Response
     */
    public function showVariables($server)
    {
        $options = $this->package->getServerOptions($server);

        $variablesInfo = $this->dbProxy->getVariables($options);
        // Make variables info available to views
        foreach($variablesInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        $content = $this->render('main/row-content');
        $this->response->html('adminer-server-main-table', $content);

        return $this->response;
    }

    /**
     * Show the status of a server
     *
     * @param string $server      The database server
     *
     * @return \Jaxon\Response\Response
     */
    public function showStatus($server)
    {
        $options = $this->package->getServerOptions($server);

        $statusInfo = $this->dbProxy->getStatus($options);
        // Make status info available to views
        foreach($statusInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        $content = $this->render('main/row-content');
        $this->response->html('adminer-server-main-table', $content);

        return $this->response;
    }

    /**
     * Select a database
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function select($server, $database)
    {
        $options = $this->package->getServerOptions($server);

        $databaseInfo = $this->dbProxy->getDatabaseInfo($options, $database);
        // Make database info available to views
        foreach($databaseInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }
        // $this->response->dialog->info(json_encode($databaseInfo), "Info");

        $content = $this->render('menu/actions');
        $this->response->html($this->package->getDbActionsId(), $content);
        $this->response->clear($this->package->getServerActionsId());

        $content = $this->render('menu/database');
        $this->response->html($this->package->getDbMenuId(), $content);
        // Set the click handlers
        $this->jq('#adminer-dbmenu-action-table')
            ->click($this->cl(Database::class)->rq()->showTables($server, $database));
        $this->jq('#adminer-dbmenu-action-routine')
            ->click($this->cl(Database::class)->rq()->showRoutines($server, $database));
        $this->jq('#adminer-dbmenu-action-sequence')
            ->click($this->cl(Database::class)->rq()->showSequences($server, $database));
        $this->jq('#adminer-dbmenu-action-type')
            ->click($this->cl(Database::class)->rq()->showUserTypes($server, $database));
        $this->jq('#adminer-dbmenu-action-event')
            ->click($this->cl(Database::class)->rq()->showEvents($server, $database));

        // Show the database tables
        $this->cl(Database::class)->showTables($server, $database);

        return $this->response;
    }
}
