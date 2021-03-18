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
     * The database list will be displaye in the select component.
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

        $content = $this->view()->render('adminer::views::menu/server', [
            'select' => $this->rq()->select($server, \pm()->select('adminer-dbname-select')),
        ]);
        $this->response->html($this->package->getDbListId(), $content);

        $content = $this->view()->render('adminer::views::menu/actions');
        $this->response->html($this->package->getServerActionsId(), $content);
        $this->response->html($this->package->getDbActionsId(), '');

        $content = $this->view()->render('adminer::views::main/server');
        $this->response->html($this->package->getDbContentId(), $content);

        return $this->response;
    }

    /**
     * Select a database
     *
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
        $this->response->html($this->package->getServerActionsId(), '');

        $content = $this->view()->render('adminer::views::menu/database', [
            'select' => $this->rq()->select($server, \pm()->select('adminer-dbname-select')),
        ]);
        $this->response->html($this->package->getDbMenuId(), $content);

        $content = $this->view()->render('adminer::views::main/database');
        $this->response->html($this->package->getDbContentId(), $content);

        return $this->response;
    }
}
