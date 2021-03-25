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

        $content = $this->render('info/user');
        $this->response->html($this->package->getUserInfoId(), $content);

        $content = $this->render('info/server');
        $this->response->html($this->package->getServerInfoId(), $content);

        $content = $this->render('menu/databases');
        $this->response->html($this->package->getDbListId(), $content);

        // Set onclick handlers on database dropdown select
        $this->jq('#adminer-dbname-select-btn')
            ->click($this->cl(Database::class)->rq()
            ->select($server, \pm()->select('adminer-dbname-select')));

        $content = $this->render('menu/actions');
        $this->response->html($this->package->getDbMenuId(), $content);

        // Set the click handlers
        $this->jq('#adminer-menu-action-databases')
            ->click($this->rq()->showDatabases($server));
        $this->jq('#adminer-menu-action-processes')
            ->click($this->rq()->showProcesses($server));
        $this->jq('#adminer-menu-action-variables')
            ->click($this->rq()->showVariables($server));
        $this->jq('#adminer-menu-action-status')
            ->click($this->rq()->showStatus($server));

        // Show the database list
        $this->showDatabases($server);

        return $this->response;
    }

    /**
     * Show the databases of a server
     *
     * @param string $server      The database server
     *
     * @return \Jaxon\Response\Response
     */
    public function showDatabases($server)
    {
        $options = $this->package->getServerOptions($server);

        $databasesInfo = $this->dbProxy->getDatabases($options);

        $dbNameClass = 'adminer-db-name';
        // Add links, classes and data values to database names.
        $databasesInfo['details'] = \array_map(function($detail) use($dbNameClass) {
            $detail['name'] = [
                'class' => $dbNameClass,
                'value' => $detail['name'],
                'label' => $detail['name'],
            ];
            return $detail;
        }, $databasesInfo['details']);

        // Make databases info available to views
        foreach($databasesInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        $content = $this->render('main/content');
        $this->response->html($this->package->getDbContentId(), $content);

        // Set onclick handlers on database names
        $this->jq('.' . $dbNameClass . '>a', '#' . $this->package->getDbContentId())
            ->click($this->cl(Database::class)->rq()
            ->select($server, \jq()->parent()->attr('data-value')));

        // Activate the sidebar menu item
        $this->jq('.list-group-item', '#'. $this->package->getDbMenuId())->removeClass('active');
        $this->jq('.menu-action-databases', '#'. $this->package->getDbMenuId())->addClass('active');

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

        $content = $this->render('main/content');
        $this->response->html($this->package->getDbContentId(), $content);

        // Activate the sidebar menu item
        $this->jq('.list-group-item', '#'. $this->package->getDbMenuId())->removeClass('active');
        $this->jq('.menu-action-processes', '#'. $this->package->getDbMenuId())->addClass('active');

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

        $content = $this->render('main/content');
        $this->response->html($this->package->getDbContentId(), $content);

        // Activate the sidebar menu item
        $this->jq('.list-group-item', '#'. $this->package->getDbMenuId())->removeClass('active');
        $this->jq('.menu-action-variables', '#'. $this->package->getDbMenuId())->addClass('active');

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

        $content = $this->render('main/content');
        $this->response->html($this->package->getDbContentId(), $content);

        // Activate the sidebar menu item
        $this->jq('.list-group-item', '#'. $this->package->getDbMenuId())->removeClass('active');
        $this->jq('.menu-action-status', '#'. $this->package->getDbMenuId())->addClass('active');

        return $this->response;
    }
}
