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
     * Create a new table
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function add($server, $database)
    {
        $tableData = $this->dbProxy->getTableData($server, $database);
        // Make data available to views
        $this->view()->shareValues($tableData);

        // Update the breadcrumbs
        $this->showBreadcrumbs();

        $formId = 'adminer-table-form';
        $tableId = 'adminer-table-meta';
        $contentId = $this->package->getDbContentId();
        $content = $this->render('table/add', \compact('formId', 'tableId'));
        $this->response->html($contentId, $content);

        // Set onclick handlers on toolbar buttons
        $this->jq('#adminer-main-action-table-cancel')
            ->click($this->cl(Database::class)->rq()->showTables($server, $database));
        $count = \jq(".$formId-column", $contentId)->length;
        $this->jq('#adminer-table-add-column')
            ->click($this->rq()->addColumn($server, $database, $count));
        // $this->jq('#adminer-table-meta-cancel')
        //     ->click($this->cl(Database::class)->rq()->showTables($server, $database));
        // $this->jq('#adminer-table-add-column')
        //     ->click($this->cl(Table\Column::class)->rq()->add($server, $database));

        return $this->response;
    }

    /**
     * Update a given table
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $table       The table name
     *
     * @return \Jaxon\Response\Response
     */
    public function edit($server, $database, $table)
    {
        $tableData = $this->dbProxy->getTableData($server, $database, $table);
        // Make data available to views
        $this->view()->shareValues($tableData);

        // Update the breadcrumbs
        $this->showBreadcrumbs();

        $formId = 'adminer-table-form';
        $tableId = 'adminer-table-meta';
        $contentId = $this->package->getDbContentId();
        $content = $this->render('table/edit', \compact('formId', 'tableId'));
        $this->response->html($contentId, $content);

        // Set onclick handlers on toolbar buttons
        $this->jq('#adminer-main-action-table-cancel')
            ->click($this->rq()->show($server, $database, $table));
        $count = \jq(".$formId-column", $contentId)->length;
        $this->jq('#adminer-table-add-column')
            ->click($this->rq()->addColumn($server, $database, $count));
        // $this->jq('#adminer-table-meta-cancel')
        //     ->click($this->rq()->show($server, $database, $table));
        // $this->jq('#adminer-table-add-column')
        //     ->click($this->cl(Table\Column::class)->rq()->add($server, $database));

        return $this->response;
    }

    /**
     * Insert a new column at a given position
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param int    $index       The number of columns in the table.
     * @param int    $before      The new column is added before this position. Set to -1 to add at the end.
     *
     * @return \Jaxon\Response\Response
     */
    public function addColumn($server, $database, $index, $before = -1)
    {
        $tableData = $this->dbProxy->getTableData($server, $database);
        // Make data available to views
        $this->view()->shareValues($tableData);

        $formId = 'adminer-table-form';
        $content = $this->render('table/field', [
            'class' => "$formId-column",
            'index' => $index,
            'field' => $this->dbProxy->getTableField($server, $database)
        ]);
        $this->response->append($formId, 'innerHTML', $content);

        return $this->response;
    }

    /**
     * Create a new table
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param array  $values      The table values
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
     * @param array  $values      The table values
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
