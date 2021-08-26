<?php

namespace Lagdo\Adminer\App;

use Lagdo\Adminer\App\Table\Column;
use Lagdo\Adminer\App\Table\Select;
use Lagdo\Adminer\App\Table\Query;
use Lagdo\Adminer\CallableClass;

use Exception;

/**
 * Adminer Ajax client
 */
class Table extends CallableClass
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
    public function show($server, $database, $schema, $table)
    {
        $tableInfo = $this->dbProxy->getTableInfo($server, $database, $schema, $table);
        // Make table info available to views
        $this->view()->shareValues($tableInfo);

        // Set main menu buttons
        $this->response->html($this->package->getMainActionsId(), $this->render('main/actions'));

        $content = $this->render('main/db-table');
        $this->response->html($this->package->getDbContentId(), $content);

        // Show fields
        $fieldsInfo = $this->dbProxy->getTableFields($server, $database, $schema, $table);
        $this->showTab($fieldsInfo, 'tab-content-fields');

        // Show indexes
        $indexesInfo = $this->dbProxy->getTableIndexes($server, $database, $schema, $table);
        if(\is_array($indexesInfo))
        {
            $this->showTab($indexesInfo, 'tab-content-indexes');
        }

        // Show foreign keys
        $foreignKeysInfo = $this->dbProxy->getTableForeignKeys($server, $database, $schema, $table);
        if(\is_array($foreignKeysInfo))
        {
            $this->showTab($foreignKeysInfo, 'tab-content-foreign-keys');
        }

        // Show triggers
        $triggersInfo = $this->dbProxy->getTableTriggers($server, $database, $schema, $table);
        if(\is_array($triggersInfo))
        {
            $this->showTab($triggersInfo, 'tab-content-triggers');
        }

        // Set onclick handlers on toolbar buttons
        $this->jq('#adminer-main-action-edit-table')
            ->click($this->rq()->edit($server, $database, $schema, $table));
        $this->jq('#adminer-main-action-drop-table')
            ->click($this->rq()->drop($server, $database, $schema, $table)
            ->confirm("Drop table $table?"));
        $this->jq('#adminer-main-action-select-table')
            ->click($this->cl(Select::class)->rq()->show($server, $database, $schema, $table));
        $this->jq('#adminer-main-action-insert-table')
            ->click($this->cl(Query::class)->rq()->showInsert($server, $database, $schema, $table));

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
    public function add($server, $database, $schema)
    {
        $tableData = $this->dbProxy->getTableData($server, $database, $schema);
        // Make data available to views
        $this->view()->shareValues($tableData);

        // Set main menu buttons
        $this->response->html($this->package->getMainActionsId(), $this->render('main/actions'));

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
            ->click($this->rq()->create($server, $database, $schema, $values)
            ->when($length));
        $this->jq('#adminer-main-action-table-cancel')
            ->click($this->cl(Database::class)->rq()->showTables($server, $database, $schema));
        $this->jq('#adminer-table-column-add')
            ->click($this->cl(Column::class)->rq()->add($server, $database, $schema, $length));

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
    public function edit($server, $database, $schema, $table)
    {
        $tableData = $this->dbProxy->getTableData($server, $database, $schema, $table);
        // Make data available to views
        $this->view()->shareValues($tableData);

        // Set main menu buttons
        $this->response->html($this->package->getMainActionsId(), $this->render('main/actions'));

        $contentId = $this->package->getDbContentId();
        $content = $this->render('table/edit', [
            'formId' => $this->formId,
            'tableId' => $this->tableId,
        ]);
        $this->response->html($contentId, $content);

        // Set onclick handlers on toolbar buttons
        $values = \pm()->form($this->formId);
        $this->jq('#adminer-main-action-table-save')
            ->click($this->rq()->alter($server, $database, $schema, $table, $values)
            ->confirm("Save changes on table $table?"));
        $this->jq('#adminer-main-action-table-cancel')
            ->click($this->rq()->show($server, $database, $schema, $table));
        $length = \jq(".{$this->formId}-column", "#$contentId")->length;
        $this->jq('#adminer-table-column-add')
            ->click($this->cl(Column::class)->rq()->add($server, $database, $schema, $length));
        $index = \jq()->parent()->attr('data-index');
        $this->jq('.adminer-table-column-add')
            ->click($this->cl(Column::class)->rq()->add($server, $database, $schema, $length, $index));
        $this->jq('.adminer-table-column-del')
            ->click($this->cl(Column::class)->rq()->setForDelete($server, $database, $schema, $index));

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
    public function create($server, $database, $schema, array $values)
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

        $result = $this->dbProxy->createTable($server, $database, $schema, $values);
        if(!$result['success'])
        {
            $this->response->dialog->error($result['error']);
            return $this->response;
        }

        $this->show($server, $database, $schema, $values['name']);
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
    public function alter($server, $database, $schema, $table, array $values)
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

        $result = $this->dbProxy->alterTable($server, $database, $schema, $table, $values);
        if(!$result['success'])
        {
            $this->response->dialog->error($result['error']);
            return $this->response;
        }

        $this->show($server, $database, $schema, $values['name']);
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
    public function drop($server, $database, $schema, $table)
    {
        $result = $this->dbProxy->dropTable($server, $database, $schema, $table);
        if(!$result['success'])
        {
            $this->response->dialog->error($result['error']);
            return $this->response;
        }

        $this->cl(Database::class)->showTables($server, $database, $schema);
        $this->response->dialog->success($result['message']);
        return $this->response;
    }
}
