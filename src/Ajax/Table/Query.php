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
        $insertData = $this->dbProxy->getQueryData($server, $database, $schema, $table);
        // Make data available to views
        $this->view()->shareValues($insertData);

        $this->showBreadcrumbs();

        $content = $this->render('table/query', [
            'formId' => $this->queryFormId,
        ]);
        $this->response->html($this->package->getDbContentId(), $content);

        // $options = \pm()->form($this->queryFormId);
        // // Set onclick handlers on buttons
        $this->jq('#adminer-main-action-insert-cancel')
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
}
