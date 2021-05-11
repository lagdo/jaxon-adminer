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
        $this->jq('#adminer-main-action-table-cancel')
            ->click($this->cl(Database::class)->rq()->showTables($server, $database));
        $length = \jq(".{$this->formId}-column", "#$contentId")->size();
        $this->jq('#adminer-table-column-add')
            ->click($this->rq()->addColumn($server, $database, $length));
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

        $contentId = $this->package->getDbContentId();
        $content = $this->render('table/edit', [
            'formId' => $this->formId,
            'tableId' => $this->tableId,
        ]);
        $this->response->html($contentId, $content);

        // Set onclick handlers on toolbar buttons
        $this->jq('#adminer-main-action-table-cancel')
            ->click($this->rq()->show($server, $database, $table));
        $length = \jq(".{$this->formId}-column", "#$contentId")->length;
        $this->jq('#adminer-table-column-add')
            ->click($this->rq()->addColumn($server, $database, $length));
        $target = \jq()->parent()->attr('data-index');
        $this->jq('.adminer-table-column-add')
            ->click($this->rq()->addColumn($server, $database, $length, $target));
        // $this->jq('#adminer-table-meta-cancel')
        //     ->click($this->rq()->show($server, $database, $table));
        // $this->jq('#adminer-table-add-column')
        //     ->click($this->cl(Table\Column::class)->rq()->add($server, $database));

        return $this->response;
    }

    /**
     * Insert a new column at a given position
     *
     * @param string $target      The target element
     * @param string $id          The new element id
     * @param string $class       The new element class
     * @param string $content     The new element content
     * @param array  $attrs       The new element attributes
     *
     * @return \Jaxon\Response\Response
     */
    public function insertColumnBefore($target, $id, $class, $content, array $attrs = [])
    {
        // Insert a div with the id before the target
        $this->response->insert($target, 'div', $id);
        // Set the new element class
        $this->jq("#$id")->attr('class', "form-group $class");
        // Set the new element attributes
        foreach($attrs as $name => $value)
        {
            $this->jq("#$id")->attr($name, $value);
        }
        // Set the new element content
        $this->response->html($id, $content);
    }

    /**
     * Insert a new column at a given position
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param int    $length      The number of columns in the table.
     * @param int    $target      The new column is added before this position. Set to -1 to add at the end.
     *
     * @return \Jaxon\Response\Response
     */
    public function addColumn($server, $database, $length, $target = -1)
    {
        $tableData = $this->dbProxy->getTableData($server, $database);
        // Make data available to views
        $this->view()->shareValues($tableData);

        $columnClass = "{$this->formId}-column";
        $columnId = \sprintf('%s-%02d', $columnClass, $length);
        $targetId = \sprintf('%s-%02d', $columnClass, $target);
        $vars = [
            'index' => $length,
            'field' => $this->dbProxy->getTableField($server, $database)
        ];
        if($target < 0)
        {
            // Get the content with wrapper
            $vars['class'] = $columnClass;
        }
        $content = $this->render('table/field', $vars);

        $contentId = $this->package->getDbContentId();
        $length = \jq(".$columnClass", "#$contentId")->length;
        $index = \jq()->parent()->attr('data-index');
        if($target < 0)
        {
            // Add the new column at the end of the list
            $this->response->append($this->formId, 'innerHTML', $content);
            // Set the button event handlers on the new column
            $this->jq('.adminer-table-column-add', "#$columnId")
                ->click($this->rq()->addColumn($server, $database, $length, $index));

            return $this->response;
        }

        // Insert the new column before the given index
        /*
         * The prepend() function is not suitable here because it rewrites the
         * $targetId element, resetting all its event handlers and inputs.
         */
        $this->insertColumnBefore($targetId, $columnId, $columnClass, $content);
        // $this->response->prepend($targetId, 'outerHTML', $content);
        // Set the button event handlers on the new and the modified column
        $this->jq('.adminer-table-column-add', "#$columnId")
            ->click($this->rq()->addColumn($server, $database, $length, $index));

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
