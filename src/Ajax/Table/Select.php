<?php

namespace Lagdo\Adminer\Ajax\Table;

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
        $btnActionId = 'adminer-table-select-action';
        $content = $this->render('table/select', [
            'formId' => $formId,
            'btnColumnsId' => $btnColumnsId,
            'btnFiltersId' => $btnFiltersId,
            'btnSortingId' => $btnSortingId,
            'btnActionId' => $btnActionId,
        ]);
        $this->response->html($this->package->getDbContentId(), $content);

        return $this->response;
    }
}
