<?php

namespace Lagdo\Adminer\Ajax;

use Lagdo\Adminer\Ajax\Table\Column;
use Lagdo\Adminer\AdminerCallable;

use Exception;

/**
 * Adminer Ajax client
 */
class Table extends AdminerCallable
{
    /**
     * The form id
     */
    protected $formId = 'adminer-table-form';

    /**
     * The table id
     */
    protected $tableId = 'adminer-table-header';

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

        $contentId = $this->package->getDbContentId();
        $content = $this->render('table/add', [
            'formId' => $this->formId,
            'tableId' => $this->tableId,
        ]);
        $this->response->html($contentId, $content);

        // Set onclick handlers on toolbar buttons
        $length = \jq(".{$this->formId}-column", "#$contentId")->length;
        $values = \pm()->form($this->formId);
        $this->jq('#adminer-main-action-table-save')
            ->click($this->rq()->create($server, $database, $values)->when($length));
        $this->jq('#adminer-main-action-table-cancel')
            ->click($this->cl(Database::class)->rq()->showTables($server, $database));
        $this->jq('#adminer-table-column-add')
            ->click($this->cl(Column::class)->rq()->add($server, $database, $length));

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

        $contentId = $this->package->getDbContentId();
        $content = $this->render('table/edit', [
            'formId' => $this->formId,
            'tableId' => $this->tableId,
        ]);
        $this->response->html($contentId, $content);

        // Set onclick handlers on toolbar buttons
        $values = \pm()->form($this->formId);
        $this->jq('#adminer-main-action-table-save')
            ->click($this->rq()->alter($server, $database, $table, $values));
        $this->jq('#adminer-main-action-table-cancel')
            ->click($this->rq()->show($server, $database, $table));
        $length = \jq(".{$this->formId}-column", "#$contentId")->length;
        $this->jq('#adminer-table-column-add')
            ->click($this->cl(Column::class)->rq()->add($server, $database, $length));
        $index = \jq()->parent()->attr('data-index');
        $this->jq('.adminer-table-column-add')
            ->click($this->cl(Column::class)->rq()->add($server, $database, $length, $index));
        $this->jq('.adminer-table-column-del')
            ->click($this->cl(Column::class)->rq()->setForDelete($server, $database, $index));

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
        if(!isset($values['comment']))
        {
            $values['comment'] = null;
        }
        if(!isset($values['engine']))
        {
            $values['engine'] = '';
        }
        if(!isset($values['collation']))
        {
            $values['collation'] = '';
        }

        $result = $this->dbProxy->createTable($server, $database, $values);
        if(!$result['success'])
        {
            $this->response->dialog->error($result['error']);
            return $this->response;
        }

        $this->show($server, $database, $values['name']);
        $this->response->dialog->success($result['message']);
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
    public function alter($server, $database, $table, array $values)
    {
        if(!isset($values['comment']))
        {
            $values['comment'] = null;
        }
        if(!isset($values['engine']))
        {
            $values['engine'] = '';
        }
        if(!isset($values['collation']))
        {
            $values['collation'] = '';
        }

        $result = $this->dbProxy->alterTable($server, $database, $table, $values);
        if(!$result['success'])
        {
            $this->response->dialog->error($result['error']);
            return $this->response;
        }

        $this->show($server, $database, $values['name']);
        $this->response->dialog->success($result['message']);
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
