<?php

namespace Lagdo\Adminer\Ajax;

use Lagdo\Adminer\Package;
use Lagdo\Adminer\AdminerCallable;
use Lagdo\Adminer\Db\Proxy as DbProxy;

use Exception;

/**
 * Adminer Ajax client
 */
class Database extends AdminerCallable
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

        $tableNameClass = 'adminer-table-name';
        // Add links, classes and data values to table names.
        $tablesInfo['details'] = \array_map(function($detail) use($tableNameClass) {
            $detail['name'] = [
                'class' => $tableNameClass,
                'value' => $detail['name'],
                'label' => $detail['name'],
            ];
            return $detail;
        }, $tablesInfo['details']);
        // \array_walk($tablesInfo['details'], function(&$detail) use($tableNameClass) {
        //     $detail['name'] = [
        //         'class' => $tableNameClass,
        //         'value' => $detail['name'],
        //         'label' => $detail['name'],
        //     ];
        // });

        // Make tables info available to views
        foreach($tablesInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        $content = $this->render('main/content');
        $this->response->html($this->package->getDbContentId(), $content);

        // Activate the sidebar menu item
        $this->jq('.list-group-item', '#'. $this->package->getDbMenuId())->removeClass('active');
        $this->jq('.menu-action-table', '#'. $this->package->getDbMenuId())->addClass('active');

        // Set onclick handlers on table names
        $table = \jq()->parent()->attr('data-value');
        $this->jq('.' . $tableNameClass . '>a', '#' . $this->package->getDbContentId())
            ->click($this->cl(Table::class)->rq()->showTable($server, $database, $table));

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

        $content = $this->render('main/content');
        $this->response->html($this->package->getDbContentId(), $content);

        // Activate the sidebar menu item
        $this->jq('.list-group-item', '#'. $this->package->getDbMenuId())->removeClass('active');
        $this->jq('.menu-action-routine', '#'. $this->package->getDbMenuId())->addClass('active');

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

        $content = $this->render('main/content');
        $this->response->html($this->package->getDbContentId(), $content);

        // Activate the sidebar menu item
        $this->jq('.list-group-item', '#'. $this->package->getDbMenuId())->removeClass('active');
        $this->jq('.menu-action-sequence', '#'. $this->package->getDbMenuId())->addClass('active');

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

        $content = $this->render('main/content');
        $this->response->html($this->package->getDbContentId(), $content);

        // Activate the sidebar menu item
        $this->jq('.list-group-item', '#'. $this->package->getDbMenuId())->removeClass('active');
        $this->jq('.menu-action-type', '#'. $this->package->getDbMenuId())->addClass('active');

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

        $content = $this->render('main/content');
        $this->response->html($this->package->getDbContentId(), $content);

        // Activate the sidebar menu item
        $this->jq('.list-group-item', '#'. $this->package->getDbMenuId())->removeClass('active');
        $this->jq('.menu-action-event', '#'. $this->package->getDbMenuId())->addClass('active');

        return $this->response;
    }
}
