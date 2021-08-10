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
     * Display the SQL command form
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $schema      The schema name
     * @param string $query       The SQL query to display
     *
     * @return \Jaxon\Response\Response
     */
    public function showCommandForm(string $server, string $database = '',
        string $schema = '', string $query = '')
    {
        $commandOptions = $this->dbProxy->prepareCommand($server, $database, $schema);

        // Make data available to views
        $this->view()->shareValues($commandOptions);

        // Update the breadcrumbs
        $this->showBreadcrumbs();

        // De-activate the sidebar menu items
        $menuId = $database === '' ? 'server' : 'database';
        $wrapperId = $database === '' ?
            $this->package->getServerActionsId() : $this->package->getDbActionsId();
        $this->selectMenuItem("#adminer-menu-action-$menuId-command", $wrapperId);

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
