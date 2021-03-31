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
     * Show the  create database dialog
     *
     * @param string $server      The database server
     *
     * @return \Jaxon\Response\Response
     */
    public function add($server)
    {
        $options = $this->package->getServerOptions($server);
        $collations = $this->dbProxy->getCollations($options);

        $formId = 'database-form';
        $title = 'Create a database';
        $content = $this->render('database/add', [
            'formId' => $formId,
            'collations' => $collations,
        ]);
        $buttons = [[
            'title' => 'Cancel',
            'class' => 'btn btn-tertiary',
            'click' => 'close',
        ],[
            'title' => 'Save',
            'class' => 'btn btn-primary',
            'click' => $this->rq()->create($server, pr()->form($formId)),
        ]];
        $this->response->dialog->show($title, $content, $buttons);
        return $this->response;
    }

    /**
     * Show the  create database dialog
     *
     * @param string $server      The database server
     * @param string $formValues  The form values
     *
     * @return \Jaxon\Response\Response
     */
    public function create($server, array $formValues)
    {
        $options = $this->package->getServerOptions($server);

        $database = $formValues['name'];
        $collation = $formValues['collation'];

        if(!$this->dbProxy->createDatabase($options, $database, $collation))
        {
            $this->response->dialog->error("Cannot create database $database.");
            return $this->response;
        }
        $this->cl(Server::class)->showDatabases($server);

        $this->response->dialog->hide();
        $this->response->dialog->info("Database $database created.");

        return $this->response;
    }

    /**
     * Drop a database
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function drop($server, $database)
    {
        $options = $this->package->getServerOptions($server);

        if(!$this->dbProxy->dropDatabase($options, $database))
        {
            $this->response->dialog->error("Cannot delete database $database.");
            return $this->response;
        }

        $this->cl(Server::class)->showDatabases($server);
        $this->response->dialog->info("Database $database deleted.");
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

        // Update the breadcrumbs
        $this->showBreadcrumbs();

        // $content = $this->render('menu/actions');
        // $this->response->html($this->package->getDbActionsId(), $content);
        // $this->response->clear($this->package->getServerActionsId());

        $content = $this->render('menu/actions');
        $this->response->html($this->package->getDbMenuId(), $content);

        // Set the click handlers
        $this->jq('#adminer-menu-action-table')
            ->click($this->rq()->showTables($server, $database));
        $this->jq('#adminer-menu-action-routine')
            ->click($this->rq()->showRoutines($server, $database));
        $this->jq('#adminer-menu-action-sequence')
            ->click($this->rq()->showSequences($server, $database));
        $this->jq('#adminer-menu-action-type')
            ->click($this->rq()->showUserTypes($server, $database));
        $this->jq('#adminer-menu-action-event')
            ->click($this->rq()->showEvents($server, $database));
        // Set the selected entry on database dropdown select
        $this->jq('#adminer-dbname-select')->val($database)->change();

        // Show the database tables
        $this->showTables($server, $database);

        return $this->response;
    }

    /**
     * Display the content of a section
     *
     * @param string $menuId    The menu item id
     * @param array  $viewData  The data to be displayed in the view
     * @param array  $contentData  The data to be displayed in the view
     *
     * @return void
     */
    protected function showSection(string $menuId, array $viewData, array $contentData = [])
    {
        // Make data available to views
        foreach($viewData as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        // Update the breadcrumbs
        $this->showBreadcrumbs();

        $content = $this->render('main/content', $contentData);
        $this->response->html($this->package->getDbContentId(), $content);

        // Activate the sidebar menu item
        $this->jq('.list-group-item', '#'. $this->package->getDbMenuId())->removeClass('active');
        $this->jq(".menu-action-$menuId", '#'. $this->package->getDbMenuId())->addClass('active');
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

        $checkbox = 'table';
        $this->showSection('table', $tablesInfo, ['checkbox' => $checkbox]);

        // Set onclick handlers on table checkbox
        $this->response->script("jaxon.adminer.selectTableCheckboxes('$checkbox')");
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
        $this->showSection('routine', $routinesInfo);

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
        $this->showSection('sequence', $sequencesInfo);

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
        $this->showSection('type', $userTypesInfo);

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
        $this->showSection('event', $eventsInfo);

        return $this->response;
    }
}
