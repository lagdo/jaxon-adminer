<?php

namespace Lagdo\Adminer\Ajax;

use Lagdo\Adminer\Package;
use Lagdo\Adminer\Db\Proxy as DbProxy;

use Jaxon\CallableClass;
use Exception;

/**
 * Adminer Ajax
 */
class Server extends CallableClass
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

        $content = $this->view()->render('adminer::views::menu/server');
        $this->response->html($this->package->getDbListId(), $content);
        $this->jq('#adminer-dbname-select-btn')
            ->click($this->rq()->select($server, \pm()->select('adminer-dbname-select')));

        $content = $this->view()->render('adminer::views::menu/actions');
        $this->response->html($this->package->getServerActionsId(), $content);
        $this->response->clear($this->package->getDbActionsId());
        $this->response->clear($this->package->getDbMenuId());

        $content = $this->view()->render('adminer::views::main/server');
        $this->response->html($this->package->getDbContentId(), $content);

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

        $content = $this->view()->render('adminer::views::menu/actions');
        $this->response->html($this->package->getDbActionsId(), $content);
        $this->response->clear($this->package->getServerActionsId());

        $content = $this->view()->render('adminer::views::menu/database');
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
