<?php

namespace Lagdo\Adminer\Ajax\Table;

use Lagdo\Adminer\Ajax\Table;
use Lagdo\Adminer\AdminerCallable;

use Exception;

/**
 * This class provides insert and update query features on tables.
 */
class Query extends AdminerCallable
{
    /**
     * The query form div id
     *
     * @var string
     */
    private $queryFormId = 'adminer-table-query-form';

    /**
     * Show the insert query form
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $schema      The schema name
     * @param string $table       The table name
     *
     * @return \Jaxon\Response\Response
     */
    public function showInsert(string $server, string $database, string $schema, string $table)
    {
        $queryData = $this->dbProxy->getQueryData($server, $database, $schema, $table);
        // Show the error
        if(($queryData['error']))
        {
            $this->response->dialog->error($queryData['error'], \adminer\lang('Error'));
            return $this->response;
        }
        // Make data available to views
        $this->view()->shareValues($queryData);

        $this->showBreadcrumbs();

        $content = $this->render('table/query', [
            'formId' => $this->queryFormId,
        ]);
        $this->response->html($this->package->getDbContentId(), $content);

        // $options = \pm()->form($this->queryFormId);
        // // Set onclick handlers on buttons
        $this->jq('#adminer-main-action-query-cancel')
            ->click($this->cl(Table::class)->rq()->show($server, $database, $schema, $table));

        return $this->response;
    }

    /**
     * Execute the insert query
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $schema      The schema name
     * @param string $table       The table name
     * @param array  $options     The query options
     *
     * @return \Jaxon\Response\Response
     */
    public function execInsert(string $server, string $database, string $schema,
        string $table, array $options)
    {
        $results = $this->dbProxy->execQuery($server, $database, $schema, $table, $options);

        $content = $this->render('table/insert/results', $results);
        $this->response->html('adminer-table-insert-results', $content);

        return $this->response;
    }

    /**
     * Show the update query form
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $schema      The schema name
     * @param string $table       The table name
     * @param integer $rowId      The query options
     * @param array  $rowIds      The query options
     *
     * @return \Jaxon\Response\Response
     */
    public function showUpdate(string $server, string $database, string $schema,
        string $table, int $rowId, array $rowIds)
    {
        $where = [];
        $null = [];
        foreach($rowIds[$rowId] ?? [] as $key => $val)
        {
            if($val === null)
            {
                $null[] = $key;
            }
            else
            {
                $where[$key] = $val;
            }
        }
        $queryData = $this->dbProxy->getQueryData($server, $database, $schema, $table,
            ['where' => $where, 'null' => $null, 'action' => 'Edit item']);
        // Show the error
        if(($queryData['error']))
        {
            $this->response->dialog->error($queryData['error'], \adminer\lang('Error'));
            return $this->response;
        }
        // Make data available to views
        $this->view()->shareValues($queryData);

        $this->showBreadcrumbs();

        $content = $this->render('table/query', [
            'formId' => $this->queryFormId,
        ]);
        $this->response->html($this->package->getDbContentId(), $content);

        // $options = \pm()->form($this->queryFormId);
        // // Set onclick handlers on buttons
        $this->jq('#adminer-main-action-query-cancel')
            ->click($this->cl(Table::class)->rq()->show($server, $database, $schema, $table));

        return $this->response;
    }

    /**
     * Execute the update query
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $schema      The schema name
     * @param string $table       The table name
     * @param array  $options     The query options
     *
     * @return \Jaxon\Response\Response
     */
    public function execUpdate(string $server, string $database, string $schema,
        string $table, array $options)
    {
        $results = $this->dbProxy->execQuery($server, $database, $schema, $table, $options);

        $content = $this->render('table/update/results', $results);
        $this->response->html('adminer-table-update-results', $content);

        return $this->response;
    }
}
