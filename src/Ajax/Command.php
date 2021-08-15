<?php

namespace Lagdo\Adminer\Ajax;

use Lagdo\Adminer\CallableClass;

use Exception;

/**
 * Adminer Ajax client
 */
class Command extends CallableClass
{
    /**
     * Show the SQL command form
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $schema      The schema name
     * @param string $query       The SQL query to display
     *
     * @return \Jaxon\Response\Response
     */
    protected function showForm(string $server, string $database, string $schema, string $query)
    {
        $commandOptions = $this->dbProxy->prepareCommand($server, $database, $schema);

        // Make data available to views
        $this->view()->shareValues($commandOptions);

        // Set main menu buttons
        $this->response->html($this->package->getMainActionsId(), $this->render('main/actions'));

        $btnId = 'adminer-main-command-execute';
        $formId = 'adminer-main-command-form';
        $queryId = 'adminer-main-command-query';

        $content = $this->render('sql/command', [
            'btnId' => $btnId,
            'formId' => $formId,
            'queryId' => $queryId,
            'defaultLimit' => 20,
            'query' => $query,
        ]);
        $this->response->html($this->package->getDbContentId(), $content);

        $this->jq("#$btnId")
            ->click($this->rq()->execute($server, $database, $schema, \pm()->form($formId))
                ->when(\pm()->input($queryId)));

        return $this->response;
    }

    /**
     * Show the SQL command form for a server
     *
     * @param string $server      The database server
     * @param string $query       The SQL query to display
     *
     * @return \Jaxon\Response\Response
     */
    public function showServerForm(string $server, string $query = '')
    {
        return $this->showForm($server, '', '', $query);
    }

    /**
     * Show the SQL command form for a database
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $schema      The schema name
     * @param string $query       The SQL query to display
     *
     * @return \Jaxon\Response\Response
     */
    public function showDatabaseForm(string $server, string $database = '',
        string $schema = '', string $query = '')
    {
        return $this->showForm($server, $database, $schema, $query);
    }

    /**
     * Execute an SQL query and display the results
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $schema      The schema name
     * @param array $formValues
     *
     * @return \Jaxon\Response\Response
     */
    public function execute(string $server, string $database, string $schema, array $formValues)
    {
        $query = \trim($formValues['query'] ?? '');
        $limit = \intval($formValues['limit'] ?? 0);
        $errorStops = $formValues['error_stops'] ?? false;
        $onlyErrors = $formValues['only_errors'] ?? false;

        if(!$query)
        {
            $this->response->dialog->error('The query string is empty!', 'Error');
            return $this->response;
        }

        $queryResults = $this->dbProxy->executeCommands($server,
            $query, $limit, $errorStops, $onlyErrors, $database, $schema);
        // $this->logger()->debug(\json_encode($queryResults));

        $content = $this->render('sql/results', $queryResults);
        $this->response->html('adminer-command-results', $content);

        return $this->response;
    }
}
