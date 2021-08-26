<?php

namespace Lagdo\Adminer\App\Table;

use Lagdo\Adminer\App\Table;
use Lagdo\Adminer\App\Command;
use Lagdo\Adminer\CallableClass;

use Exception;

/**
 * This class provides select query features on tables.
 */
class Select extends CallableClass
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

        // Set main menu buttons
        $this->response->html($this->package->getMainActionsId(), $this->render('main/actions'));

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
        $this->jq('#adminer-main-action-select-cancel')
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
        $this->jq('#adminer-main-action-select-exec')
            ->click($this->rq()->execSelect($server, $database, $schema, $table, $options));
        $this->jq("#$btnExecId")
            ->click($this->rq()->execSelect($server, $database, $schema, $table, $options));
        $query = \jq('#' . $this->txtQueryId)->text();
        $this->jq("#$btnEditId")
            ->click($this->cl(Command::class)->rq()->showDatabaseForm($server, $database, $schema, $query));

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
     * @param integer $page       The page number
     *
     * @return \Jaxon\Response\Response
     */
    public function execSelect(string $server, string $database, string $schema,
        string $table, array $options, int $page = 1)
    {
        $options['page'] = $page;
        $results = $this->dbProxy->execSelect($server, $database, $schema, $table, $options);
        // Show the error
        if(($results['error']))
        {
            $this->response->dialog->error($results['error'], \adminer\lang('Error'));
            return $this->response;
        }
        // Make data available to views
        $this->view()->shareValues($results);

        // Set ids for row update/delete
        $rowIds = [];
        foreach($results['rows'] as $row)
        {
            $rowIds[] = $row["ids"];
        }
        // Note: don't use the var keyword when setting a variable,
        // because it will not make the variable globally accessible.
        $this->response->script("rowIds = JSON.parse('" . json_encode($rowIds) . "')");

        $resultsId = 'adminer-table-select-results';
        $btnEditRowClass = 'adminer-table-select-row-edit';
        $btnDeleteRowClass = 'adminer-table-select-row-delete';
        $content = $this->render('table/select/results', [
            'rowIds' => $rowIds,
            'btnEditRowClass' => $btnEditRowClass,
            'btnDeleteRowClass' => $btnDeleteRowClass,
        ]);
        $this->response->html($resultsId, $content);

        // The Jaxon ajax calls
        $updateCall = $this->cl(Query::class)->rq()->showUpdate($server, $database, $schema, $table,
            \pm()->js("rowIds[rowId]"), $options);
        $deleteCall = $this->cl(Query::class)->rq()->execDelete($server, $database, $schema, $table,
            \pm()->js("rowIds[rowId]"), $options)->confirm(\adminer\lang('Delete this item?'));

        // Wrap the ajax calls into functions
        $this->response->setFunction('updateRowItem', 'rowId', $updateCall);
        $this->response->setFunction('deleteRowItem', 'rowId', $deleteCall);

        // Set the functions as button event handlers
        $this->jq(".$btnEditRowClass", "#$resultsId")
            ->click(\rq()->func('updateRowItem', \jq()->parent()->attr('data-row-id')));
        $this->jq(".$btnDeleteRowClass", "#$resultsId")
            ->click(\rq()->func('deleteRowItem', \jq()->parent()->attr('data-row-id')));

        // Update the query
        $this->response->html($this->txtQueryId, $results['query']);

        // Pagination
        $pagination = $this->rq()->execSelect($server, $database, $schema, $table, $options, \pm()->page())
            ->paginate($page, $results['limit'], $results['total']);
        $this->response->html("adminer-table-select-pagination", $pagination);

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
        $options['columns'] = $changed['columns'] ?? [];
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
        $options['where'] = $changed['where'] ?? [];
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
        $options['order'] = $changed['order'] ?? [];
        $options['desc'] = $changed['desc'] ?? [];
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
