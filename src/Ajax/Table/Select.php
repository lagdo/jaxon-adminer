<?php

namespace Lagdo\Adminer\Ajax\Table;

use Lagdo\Adminer\Ajax\Table;
use Lagdo\Adminer\AdminerCallable;

use Exception;

/**
 * This class provides select query features on tables.
 */
class Select extends AdminerCallable
{
    /**
     * Delete a column
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $schema      The schema name
     * @param string $table       The table name
     *
     * @return \Jaxon\Response\Response
     */
    public function show($server, $database, $schema, $table)
    {
        $selectData = $this->dbProxy->getSelectData($server, $database, $schema, $table);
        // Make data available to views
        $this->view()->shareValues($selectData);

        $this->showBreadcrumbs();

        $formId = 'adminer-table-select-form';
        $btnColumnsId = 'adminer-table-select-columns';
        $btnFiltersId = 'adminer-table-select-filters';
        $btnSortingId = 'adminer-table-select-sorting';
        $btnEditId = 'adminer-table-select-edit';
        $btnExecId = 'adminer-table-select-exec';
        $btnLimitId = 'adminer-table-select-limit';
        $btnLengthId = 'adminer-table-select-length';
        $content = $this->render('table/select', [
            'formId' => $formId,
            'btnColumnsId' => $btnColumnsId,
            'btnFiltersId' => $btnFiltersId,
            'btnSortingId' => $btnSortingId,
            'btnEditId' => $btnEditId,
            'btnExecId' => $btnExecId,
            'btnLimitId' => $btnLimitId,
            'btnLengthId' => $btnLengthId,
        ]);
        $this->response->html($this->package->getDbContentId(), $content);

        // Set onclick handlers on buttons
        $this->jq('#adminer-main-action-select-back')
            ->click($this->cl(Table::class)->rq()->show($server, $database, $schema, $table));
        // $this->jq('#adminer-main-action-select-edit')
        //     ->click($this->rq()->drop($server, $database, $schema, $table));
        // $this->jq('#adminer-main-action-select-table')
        //     ->click($this->rq()->show($server, $database, $schema, $table));
        // $this->jq('#adminer-main-action-insert-table')
        //     ->click($this->rq()->edit($server, $database, $schema, $table));
        // $this->jq('#adminer-main-action-select-table')
        //     ->click($this->rq()->show($server, $database, $schema, $table));
        // $this->jq('#adminer-main-action-insert-table')
        //     ->click($this->rq()->edit($server, $database, $schema, $table));

        return $this->response;
    }
}
