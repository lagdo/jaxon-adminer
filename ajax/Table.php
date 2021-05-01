<?php

namespace Lagdo\Adminer\Ajax;

use Lagdo\Adminer\AdminerCallable;

use Exception;

/**
 * Adminer Ajax client
 */
class Table extends AdminerCallable
{
    /**
     * Display the content of a tab
     *
     * @param array  $tableData The data to be displayed in the view
     * @param string $tabId     The tab container id
     *
     * @return void
     */
    protected function showTab(array $tableData, string $tabId)
    {
        // Make data available to views
        $this->view()->shareValues($tableData);

        $content = $this->render('main/content');
        $this->response->html($tabId, $content);
    }

    /**
     * Show detailed info of a given table
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $table       The table name
     *
     * @return \Jaxon\Response\Response
     */
    public function show($server, $database, $table)
    {
        $tableInfo = $this->dbProxy->getTableInfo($server, $database, $table);
        // Make table info available to views
        $this->view()->shareValues($tableInfo);

        // Update the breadcrumbs
        $this->showBreadcrumbs();

        $content = $this->render('main/db-table');
        $this->response->html($this->package->getDbContentId(), $content);

        // Show fields
        $fieldsInfo = $this->dbProxy->getTableFields($server, $database, $table);
        $this->showTab($fieldsInfo, 'tab-content-fields');

        // Show indexes
        $indexesInfo = $this->dbProxy->getTableIndexes($server, $database, $table);
        if(\is_array($indexesInfo))
        {
            $this->showTab($indexesInfo, 'tab-content-indexes');
        }

        // Show foreign keys
        $foreignKeysInfo = $this->dbProxy->getTableForeignKeys($server, $database, $table);
        if(\is_array($foreignKeysInfo))
        {
            $this->showTab($foreignKeysInfo, 'tab-content-foreign-keys');
        }

        // Show triggers
        $triggersInfo = $this->dbProxy->getTableTriggers($server, $database, $table);
        if(\is_array($triggersInfo))
        {
            $this->showTab($triggersInfo, 'tab-content-triggers');
        }

        // Set onclick handlers on toolbar buttons
        $this->jq('#adminer-main-action-edit-table')
            ->click($this->rq()->edit($server, $database, $table));
        $this->jq('#adminer-main-action-drop-table')
            ->click($this->rq()->drop($server, $database, $table)
            ->confirm("Drop table $table?"));

        return $this->response;
    }

    /**
     * Show the new table form
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function add($server, $database)
    {
        $formId = 'table-form';
        $title = 'Create a table';
        $content = $this->render('table/add', [
            'formId' => $formId,
        ]);
        $buttons = [[
            'title' => 'Cancel',
            'class' => 'btn btn-tertiary',
            'click' => 'close',
        ],[
            'title' => 'Save',
            'class' => 'btn btn-primary',
            'click' => $this->rq()->create($server, $database, \pm()->form($formId)),
        ]];
        $this->response->dialog->show($title, $content, $buttons);

        return $this->response;
    }

    /**
     * Show edit form for a given table
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $table       The table name
     *
     * @return \Jaxon\Response\Response
     */
    public function edit($server, $database, $table)
    {

        return $this->response;
    }

    /**
     * Create a new table
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $values      The table values
     *
     * @return \Jaxon\Response\Response
     */
    public function create($server, $database, array $values)
    {

        return $this->response;
    }

    /**
     * Update a given table
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $table       The table name
     * @param string $values      The table values
     *
     * @return \Jaxon\Response\Response
     */
    public function update($server, $database, $table, array $values)
    {

        return $this->response;
    }

    /**
     * Drop a given table
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $table       The table name
     *
     * @return \Jaxon\Response\Response
     */
    public function drop($server, $database, $table)
    {

        return $this->response;
    }
}
