<?php

namespace Lagdo\Adminer\Ajax\Table;

use Lagdo\Adminer\Ajax\Table;
use Lagdo\Adminer\Ajax\Command;
use Lagdo\Adminer\AdminerCallable;

use Exception;

/**
 * This class provides select query features on tables.
 */
class Select extends AdminerCallable
{
    /**
     * The select form div id
     *
     * @var string
     */
    private $selectFormId = 'adminer-table-select-form';

    /**
     * The columns form div id
     *
     * @var string
     */
    private $columnsFormId = 'adminer-table-select-columns-form';

    /**
     * The filters form div id
     *
     * @var string
     */
    private $filtersFormId = 'adminer-table-select-filters-form';

    /**
     * The sorting form div id
     *
     * @var string
     */
    private $sortingFormId = 'adminer-table-select-sorting-form';

    /**
     * The select query div id
     *
     * @var string
     */
    private $txtQueryId = 'adminer-table-select-query';

    /**
     * Show the select query form
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $schema      The schema name
     * @param string $table       The table name
     *
     * @return \Jaxon\Response\Response
     */
    public function show(string $server, string $database, string $schema, string $table)
    {
        $selectData = $this->dbProxy->getSelectData($server, $database, $schema, $table);
        // Make data available to views
        $this->view()->shareValues($selectData);

        $this->showBreadcrumbs();

        $btnColumnsId = 'adminer-table-select-columns';
        $btnFiltersId = 'adminer-table-select-filters';
        $btnSortingId = 'adminer-table-select-sorting';
        $btnEditId = 'adminer-table-select-edit';
        $btnExecId = 'adminer-table-select-exec';
        $btnLimitId = 'adminer-table-select-limit';
        $btnLengthId = 'adminer-table-select-length';
        $content = $this->render('table/select', [
            'formId' => $this->selectFormId,
            'btnColumnsId' => $btnColumnsId,
            'btnFiltersId' => $btnFiltersId,
            'btnSortingId' => $btnSortingId,
            'btnEditId' => $btnEditId,
            'btnExecId' => $btnExecId,
            'btnLimitId' => $btnLimitId,
            'btnLengthId' => $btnLengthId,
            'txtQueryId' => $this->txtQueryId,
        ]);
        $this->response->html($this->package->getDbContentId(), $content);

        $options = \pm()->form($this->selectFormId);
        // Set onclick handlers on buttons
        $this->jq('#adminer-main-action-select-back')
            ->click($this->cl(Table::class)->rq()->show($server, $database, $schema, $table));
        $this->jq("#$btnColumnsId")
            ->click($this->rq()->editColumns($server, $database, $schema, $table, $options));
        $this->jq("#$btnFiltersId")
            ->click($this->rq()->editFilters($server, $database, $schema, $table, $options));
        $this->jq("#$btnSortingId")
            ->click($this->rq()->editSorting($server, $database, $schema, $table, $options));
        $this->jq("#$btnLimitId")
            ->click($this->rq()->setQueryOptions($server, $database, $schema, $table, $options));
        $this->jq("#$btnLengthId")
            ->click($this->rq()->setQueryOptions($server, $database, $schema, $table, $options));
        $query = \jq('#' . $this->txtQueryId)->text();
        $this->jq("#$btnEditId")
            ->click($this->cl(Command::class)->rq()->showCommandForm($server, $database, $schema, $query));

        return $this->response;
    }

    /**
     * Execute the query
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $schema      The schema name
     * @param string $table       The table name
     * @param array  $options     The query options
     *
     * @return \Jaxon\Response\Response
     */
    public function exec(string $server, string $database, string $schema,
        string $table, array $options)
    {

        return $this->response;
    }

    /**
     * Change the query options
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $schema      The schema name
     * @param string $table       The table name
     * @param array  $options     The query options
     *
     * @return \Jaxon\Response\Response
     */
    public function setQueryOptions(string $server, string $database, string $schema,
        string $table, array $options)
    {
        $selectData = $this->dbProxy->getSelectData($server, $database, $schema, $table, $options);
        // Display the new query
        $this->response->html($this->txtQueryId, $selectData['query']);

        return $this->response;
    }

    /**
     * Change the query columns
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $schema      The schema name
     * @param string $table       The table name
     * @param array  $options     The query options
     *
     * @return \Jaxon\Response\Response
     */
    public function editColumns(string $server, string $database, string $schema,
        string $table, array $options)
    {
        $selectData = $this->dbProxy->getSelectData($server, $database, $schema, $table, $options);
        // Make data available to views
        // $this->view()->shareValues($selectData);

        // For handlers on buttons
        $targetId = $this->columnsFormId;
        $sourceId = "$targetId-item-template";
        $checkboxClass = "$targetId-item-checkbox";

        $title = 'Edit columns';
        $content = $this->render('table/select/columns-edit', [
            'formId' => $this->columnsFormId,
            'options' => $selectData['options']['columns'],
            'btnAdd' => "jaxon.adminer.insertSelectQueryItem('$targetId', '$sourceId')",
            'btnDel' => "jaxon.adminer.removeSelectQueryItems('$targetId', '$checkboxClass')"
        ]);
        $buttons = [[
            'title' => 'Cancel',
            'class' => 'btn btn-tertiary',
            'click' => 'close',
        ],[
            'title' => 'Save',
            'class' => 'btn btn-primary',
            'click' => $this->rq()->saveColumns($server, $database, $schema, $table,
                \pm()->form($this->selectFormId), \pm()->form($this->columnsFormId)),
        ]];
        $this->response->dialog->show($title, $content, $buttons);

        $count = \count($selectData['options']['columns']['values']);
        $this->response->script("jaxon.adminer.newItemIndex=$count");

        return $this->response;
    }

    /**
     * Change the query columns
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $schema      The schema name
     * @param string $table       The table name
     * @param array  $options     The current query options
     * @param array  $changed     The changed query options
     *
     * @return \Jaxon\Response\Response
     */
    public function saveColumns(string $server, string $database, string $schema,
        string $table, array $options, array $changed)
    {
        $options['columns'] = \array_values($changed['columns'] ?? []);
        $selectData = $this->dbProxy->getSelectData($server, $database, $schema, $table, $options);

        // Hide the dialog
        $this->response->dialog->hide();

        // Display the new values
        $content = $this->render('table/select/columns-show', [
            'options' => $selectData['options']['columns'],
        ]);
        $this->response->html('adminer-table-select-columns-show', $content);
        // Display the new query
        $this->response->html($this->txtQueryId, $selectData['query']);

        return $this->response;
    }

    /**
     * Change the query filters
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $schema      The schema name
     * @param string $table       The table name
     * @param array  $options     The query options
     *
     * @return \Jaxon\Response\Response
     */
    public function editFilters(string $server, string $database, string $schema,
        string $table, array $options)
    {
        $selectData = $this->dbProxy->getSelectData($server, $database, $schema, $table, $options);
        // Make data available to views
        // $this->view()->shareValues($selectData);

        // For handlers on buttons
        $targetId = $this->filtersFormId;
        $sourceId = "$targetId-item-template";
        $checkboxClass = "$targetId-item-checkbox";

        $title = 'Edit filters';
        $content = $this->render('table/select/filters-edit', [
            'formId' => $this->filtersFormId,
            'options' => $selectData['options']['filters'],
            'btnAdd' => "jaxon.adminer.insertSelectQueryItem('$targetId', '$sourceId')",
            'btnDel' => "jaxon.adminer.removeSelectQueryItems('$targetId', '$checkboxClass')"
        ]);
        $buttons = [[
            'title' => 'Cancel',
            'class' => 'btn btn-tertiary',
            'click' => 'close',
        ],[
            'title' => 'Save',
            'class' => 'btn btn-primary',
            'click' => $this->rq()->saveFilters($server, $database, $schema, $table,
                \pm()->form($this->selectFormId), \pm()->form($this->filtersFormId)),
        ]];
        $this->response->dialog->show($title, $content, $buttons);

        $count = \count($selectData['options']['filters']['values']);
        $this->response->script("jaxon.adminer.newItemIndex=$count");

        return $this->response;
    }

    /**
     * Change the query filters
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $schema      The schema name
     * @param string $table       The table name
     * @param array  $options     The current query options
     * @param array  $changed     The changed query options
     *
     * @return \Jaxon\Response\Response
     */
    public function saveFilters(string $server, string $database, string $schema,
        string $table, array $options, array $changed)
    {
        $options['where'] = \array_values($changed['where'] ?? []);
        $selectData = $this->dbProxy->getSelectData($server, $database, $schema, $table, $options);

        // Hide the dialog
        $this->response->dialog->hide();

        // Display the new values
        $content = $this->render('table/select/filters-show', [
            'options' => $selectData['options']['filters'],
        ]);
        $this->response->html('adminer-table-select-filters-show', $content);
        // Display the new query
        $this->response->html($this->txtQueryId, $selectData['query']);

        return $this->response;
    }

    /**
     * Change the query sorting
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $schema      The schema name
     * @param string $table       The table name
     * @param array  $options     The query options
     *
     * @return \Jaxon\Response\Response
     */
    public function editSorting(string $server, string $database, string $schema,
        string $table, array $options)
    {
        $selectData = $this->dbProxy->getSelectData($server, $database, $schema, $table, $options);
        // Make data available to views
        // $this->view()->shareValues($selectData);

        // For handlers on buttons
        $targetId = $this->sortingFormId;
        $sourceId = "$targetId-item-template";
        $checkboxClass = "$targetId-item-checkbox";

        $title = 'Edit order';
        $content = $this->render('table/select/sorting-edit', [
            'formId' => $this->sortingFormId,
            'options' => $selectData['options']['sorting'],
            'btnAdd' => "jaxon.adminer.insertSelectQueryItem('$targetId', '$sourceId')",
            'btnDel' => "jaxon.adminer.removeSelectQueryItems('$targetId', '$checkboxClass')"
        ]);
        $buttons = [[
            'title' => 'Cancel',
            'class' => 'btn btn-tertiary',
            'click' => 'close',
        ],[
            'title' => 'Save',
            'class' => 'btn btn-primary',
            'click' => $this->rq()->saveSorting($server, $database, $schema, $table,
                \pm()->form($this->selectFormId), \pm()->form($this->sortingFormId)),
        ]];
        $this->response->dialog->show($title, $content, $buttons);

        $count = \count($selectData['options']['sorting']['values']);
        $this->response->script("jaxon.adminer.newItemIndex=$count");

        return $this->response;
    }

    /**
     * Change the query sorting
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $schema      The schema name
     * @param string $table       The table name
     * @param array  $options     The current query options
     * @param array  $changed     The changed query options
     *
     * @return \Jaxon\Response\Response
     */
    public function saveSorting(string $server, string $database, string $schema,
        string $table, array $options, array $changed)
    {
        $options['order'] = \array_values($changed['order'] ?? []);
        $options['desc'] = \array_values($changed['desc'] ?? []);
        $selectData = $this->dbProxy->getSelectData($server, $database, $schema, $table, $options);

        // Hide the dialog
        $this->response->dialog->hide();

        // Display the new values
        $content = $this->render('table/select/sorting-show', [
            'options' => $selectData['options']['sorting'],
        ]);
        $this->response->html('adminer-table-select-sorting-show', $content);
        // Display the new query
        $this->response->html($this->txtQueryId, $selectData['query']);

        return $this->response;
    }
}
