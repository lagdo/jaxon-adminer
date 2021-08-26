<?php

namespace Lagdo\Adminer\App;

use Lagdo\Adminer\CallableClass;

use Exception;

/**
 * Adminer Ajax
 */
class Server extends CallableClass
{
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
        $serverInfo = $this->dbAdmin->getServerInfo($server);
        // Make server info available to views
        $this->view()->shareValues($serverInfo);

        $content = $this->render('info/user');
        $this->response->html($this->package->getUserInfoId(), $content);

        $content = $this->render('info/server');
        $this->response->html($this->package->getServerInfoId(), $content);

        $content = $this->render('menu/databases');
        $this->response->html($this->package->getDbListId(), $content);

        // Hide schema list
        // $this->response->assign($this->package->getSchemaListId(), 'style.display', 'none');
        $this->response->clear($this->package->getSchemaListId());

        // Set onclick handlers on database dropdown select
        $database = \pm()->select('adminer-dbname-select');
        $this->jq('#adminer-dbname-select-btn')
            ->click($this->cl(Database::class)->rq()->select($server, $database)->when($database));

        if($this->checkServerAccess($server, false))
        {
            // Show the server
            return $this->showServer($server);
        }

        // Access to servers is forbidden. Show the first database.
        if(\count($serverInfo['databases']) > 0)
        {
            $database = $serverInfo['databases'][0];
            $this->cl(Database::class)->select($server, $database);
        }

        return $this->response;
    }

    /**
     * Show a db server.
     *
     * @param string $server      The database server
     *
     * @return \Jaxon\Response\Response
     */
    protected function showServer($server)
    {
        $content = $this->render('menu/commands');
        $this->response->html($this->package->getServerActionsId(), $content);
        $this->response->html($this->package->getDbActionsId(), '');

        // Set the click handlers
        $this->jq('#adminer-menu-action-server-command')
            ->click($this->cl(Command::class)->rq()->showServerForm($server));
        $this->jq('#adminer-menu-action-server-import')
            ->click($this->cl(Import::class)->rq()->showServerForm($server));
        $this->jq('#adminer-menu-action-server-export')
            ->click($this->cl(Export::class)->rq()->showServerForm($server));

        $content = $this->render('menu/actions');
        $this->response->html($this->package->getDbMenuId(), $content);

        // Set the click handlers
        $this->jq('#adminer-menu-action-databases')
            ->click($this->rq()->showDatabases($server));
        $this->jq('#adminer-menu-action-privileges')
            ->click($this->rq()->showPrivileges($server));
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
        if(!$this->checkServerAccess($server))
        {
            return $this->response;
        }

        $databasesInfo = $this->dbAdmin->getDatabases($server);

        $dbNameClass = 'adminer-database-name';
        $dbDropClass = 'adminer-database-drop';
        // Add links, classes and data values to database names.
        $databasesInfo['details'] = \array_map(function($detail) use($dbNameClass, $dbDropClass) {
            $name = $detail['name'];
            $detail['name'] = [
                'label' => '<a href="javascript:void(0)">' . $name . '</a>',
                'props' => [
                    'class' => $dbNameClass,
                    'data-name' => $name,
                ],
            ];
            $detail['drop'] = [
                'label' => '<a href="javascript:void(0)">Drop</a>',
                'props' => [
                    'class' => $dbDropClass,
                    'data-name' => $name,
                ],
            ];
            return $detail;
        }, $databasesInfo['details']);

        // Make databases info available to views
        $this->view()->shareValues($databasesInfo);

        // Set main menu buttons
        $this->response->html($this->package->getMainActionsId(), $this->render('main/actions'));

        // Add checkboxes to database table
        $checkbox = 'database';
        $content = $this->render('main/content', ['checkbox' => $checkbox]);
        $this->response->html($this->package->getDbContentId(), $content);

        // Set onclick handlers on table checkbox
        $this->response->script("jaxon.adminer.selectTableCheckboxes('$checkbox')");

        // Set onclick handlers on toolbar buttons
        $this->jq('#adminer-main-action-add-database')
            ->click($this->cl(Database::class)->rq()->add($server));

        // Set onclick handlers on database names
        $database = \jq()->parent()->attr('data-name');
        $this->jq('.' . $dbNameClass . '>a', '#' . $this->package->getDbContentId())
            ->click($this->cl(Database::class)->rq()->select($server, $database));

        // Set onclick handlers on database drop
        $database = \jq()->parent()->attr('data-name');
        $this->jq('.' . $dbDropClass . '>a', '#' . $this->package->getDbContentId())
            ->click($this->cl(Database::class)->rq()->drop($server, $database)
            ->confirm("Delete database {1}?", $database));

        return $this->response;
    }

    /**
     * Show the privileges of a server
     *
     * @param string $server      The database server
     *
     * @return \Jaxon\Response\Response
     */
    public function showPrivileges($server)
    {
        if(!$this->checkServerAccess($server))
        {
            return $this->response;
        }

        $privilegesInfo = $this->dbAdmin->getPrivileges($server);

        $editClass = 'adminer-privilege-name';
        $optionClass = 'jaxon-adminer-grant';
        // Add links, classes and data values to privileges.
        $privilegesInfo['details'] = \array_map(function($detail) use($editClass, $optionClass) {
            // Set the grant select options.
            $detail['grants'] = $this->render('html/select', [
                'options' => $detail['grants'],
                'optionClass' => $optionClass,
            ]);
            // Set the Edit button.
            $detail['edit'] = [
                'label' => '<a href="javascript:void(0)">Edit</a>',
                'props' => [
                    'class' => $editClass,
                    'data-user' => $detail['user'],
                    'data-host' => $detail['host'],
                ],
            ];
            return $detail;
        }, $privilegesInfo['details']);

        // Make privileges info available to views
        $this->view()->shareValues($privilegesInfo);

        // Set main menu buttons
        $this->response->html($this->package->getMainActionsId(), $this->render('main/actions'));

        $content = $this->render('main/content');
        $this->response->html($this->package->getDbContentId(), $content);

        // Set onclick handlers on database names
        $user = \jq()->parent()->attr('data-user');
        $host = \jq()->parent()->attr('data-host');
        $database = \jq()->parent()->parent()->find("option.$optionClass:selected")->val();
        $this->jq('.' . $editClass . '>a', '#' . $this->package->getDbContentId())
            ->click($this->cl(User::class)->rq()->edit($server, $user, $host, $database));

        // Set onclick handlers on toolbar buttons
        $this->jq('#adminer-main-action-add-user')
            ->click($this->cl(User::class)->rq()->add($server));

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
        if(!$this->checkServerAccess($server))
        {
            return $this->response;
        }

        $processesInfo = $this->dbAdmin->getProcesses($server);
        // Make processes info available to views
        $this->view()->shareValues($processesInfo);

        // Set main menu buttons
        $this->response->html($this->package->getMainActionsId(), $this->render('main/actions'));

        $content = $this->render('main/content');
        $this->response->html($this->package->getDbContentId(), $content);

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
        if(!$this->checkServerAccess($server))
        {
            return $this->response;
        }

        $variablesInfo = $this->dbAdmin->getVariables($server);
        // Make variables info available to views
        $this->view()->shareValues($variablesInfo);

        // Set main menu buttons
        $this->response->html($this->package->getMainActionsId(), $this->render('main/actions'));

        $content = $this->render('main/content');
        $this->response->html($this->package->getDbContentId(), $content);

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
        if(!$this->checkServerAccess($server))
        {
            return $this->response;
        }

        $statusInfo = $this->dbAdmin->getStatus($server);
        // Make status info available to views
        $this->view()->shareValues($statusInfo);

        // Set main menu buttons
        $this->response->html($this->package->getMainActionsId(), $this->render('main/actions'));

        $content = $this->render('main/content');
        $this->response->html($this->package->getDbContentId(), $content);

        return $this->response;
    }
}
