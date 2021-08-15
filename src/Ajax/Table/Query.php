<?php

namespace Lagdo\Adminer\Ajax\Table;

use Lagdo\Adminer\Ajax\Table;
use Lagdo\Adminer\CallableClass;

use Exception;

/**
 * This class provides insert and update query features on tables.
 */
class Query extends CallableClass
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

        // Set main menu buttons
        $this->response->html($this->package->getMainActionsId(), $this->render('main/actions'));

        $content = $this->render('table/query', [
            'formId' => $this->queryFormId,
        ]);
        $this->response->html($this->package->getDbContentId(), $content);

        $options = \pm()->form($this->queryFormId);
        // Set onclick handlers on buttons
        $this->jq('#adminer-main-action-query-save')
            ->click($this->rq()->execInsert($server, $database, $schema, $table, $options)
            ->confirm(\adminer\lang('Save this item?')));
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
        $results = $this->dbProxy->insertItem($server, $database, $schema, $table, $options);

        // Show the error
        if(($results['error']))
        {
            $this->response->dialog->error($results['error'], \adminer\lang('Error'));
            return $this->response;
        }
        $this->response->dialog->success($results['message'], \adminer\lang('Success'));
        $this->showInsert($server, $database, $schema, $table);

        return $this->response;
    }

    /**
     * Show the update query form
     *
     * @param string $server        The database server
     * @param string $database      The database name
     * @param string $schema        The schema name
     * @param string $table         The table name
     * @param array  $rowIds        The row identifiers
     * @param array  $selects       The select options
     *
     * @return \Jaxon\Response\Response
     */
    public function showUpdate(string $server, string $database, string $schema,
        string $table, array $rowIds, array $selects)
    {
        $queryData = $this->dbProxy->getQueryData($server, $database, $schema, $table, $rowIds, 'Edit item');
        // Show the error
        if(($queryData['error']))
        {
            $this->response->dialog->error($queryData['error'], \adminer\lang('Error'));
            return $this->response;
        }
        // Make data available to views
        $this->view()->shareValues($queryData);

        // Set main menu buttons
        $this->response->html($this->package->getMainActionsId(), $this->render('main/actions'));

        $content = $this->render('table/query', [
            'formId' => $this->queryFormId,
        ]);
        $this->response->html($this->package->getDbContentId(), $content);

        $options = \pm()->form($this->queryFormId);
        // Set onclick handlers on buttons
        $this->jq('#adminer-main-action-query-save')
            ->click($this->rq()->execUpdate($server, $database, $schema, $table, $rowIds, $options, $selects)
            ->confirm(\adminer\lang('Save this item?')));
        $this->jq('#adminer-main-action-query-cancel')
            ->click($this->rq()->backToSelect($server, $database, $schema, $table, $selects));

        return $this->response;
    }

    /**
     * Execute the update query
     *
     * @param string $server        The database server
     * @param string $database      The database name
     * @param string $schema        The schema name
     * @param string $table         The table name
     * @param array  $rowIds        The row selector
     * @param array  $options       The query options
     * @param array  $selects       The select options
     *
     * @return \Jaxon\Response\Response
     */
    public function execUpdate(string $server, string $database, string $schema,
        string $table, array $rowIds, array $options, array $selects)
    {
        $options['where'] = $rowIds['where'];
        $options['null'] = $rowIds['null'];
        $results = $this->dbProxy->updateItem($server, $database, $schema, $table, $options);

        // Show the error
        if(($results['error']))
        {
            $this->response->dialog->error($results['error'], \adminer\lang('Error'));
            return $this->response;
        }
        $this->response->dialog->success($results['message'], \adminer\lang('Success'));
        $this->backToSelect($server, $database, $schema, $table, $selects);

        return $this->response;
    }

    /**
     * Execute the delete query
     *
     * @param string $server        The database server
     * @param string $database      The database name
     * @param string $schema        The schema name
     * @param string $table         The table name
     * @param array  $rowIds        The row identifiers
     * @param array  $selects       The select options
     *
     * @return \Jaxon\Response\Response
     */
    public function execDelete(string $server, string $database, string $schema,
        string $table, array $rowIds, array $selects)
    {
        $results = $this->dbProxy->deleteItem($server, $database, $schema, $table, $rowIds);

        // Show the error
        if(($results['error']))
        {
            $this->response->dialog->error($results['error'], \adminer\lang('Error'));
            return $this->response;
        }
        $this->response->dialog->success($results['message'], \adminer\lang('Success'));
        $this->backToSelect($server, $database, $schema, $table, $selects);

        return $this->response;
    }

    /**
     * Get back to the select query from which the update or delete was called
     *
     * @param string $server        The database server
     * @param string $database      The database name
     * @param string $schema        The schema name
     * @param string $table         The table name
     * @param array  $options       The query options
     *
     * @return \Jaxon\Response\Response
     */
    public function backToSelect(string $server, string $database, string $schema, string $table, array $options)
    {
        $select = $this->cl(Select::class);
        $select->show($server, $database, $schema, $table);
        $select->execSelect($server, $database, $schema, $table, $options);

        return $this->response;
    }
}
