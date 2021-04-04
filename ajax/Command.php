<?php

namespace Lagdo\Adminer\Ajax;

use Lagdo\Adminer\AdminerCallable;

use Exception;

/**
 * Adminer Ajax client
 */
class Command extends AdminerCallable
{
    /**
     * Display the SQL command form
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function showCommandForm(string $server, string $database = '')
    {
        $options = $this->package->getServerOptions($server);

        $this->dbProxy->prepareCommand($options, $database);

        // Update the breadcrumbs
        $this->showBreadcrumbs();

        // De-activate the sidebar menu items
        $this->jq('.list-group-item', '#'. $this->package->getDbMenuId())->removeClass('active');

        $btnId = 'adminer-main-command-execute';
        $formId = 'adminer-main-command-form';
        $queryId = 'adminer-main-command-query';

        $content = $this->render('sql/command', [
            'btnId' => $btnId,
            'formId' => $formId,
            'queryId' => $queryId,
            'defaultLimit' => 20,
        ]);
        $this->response->html($this->package->getDbContentId(), $content);

        $this->jq("#$btnId")
            ->click($this->rq()->execute($server, $database, \pm()->form($formId))
                ->when(\pm()->input($queryId)));

        return $this->response;
    }

    /**
     * Execute an SQL query and display the results
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param array $formValues
     *
     * @return \Jaxon\Response\Response
     */
    public function execute(string $server, string $database, array $formValues)
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

        $options = $this->package->getServerOptions($server);

        $queryResults = $this->dbProxy->executeCommand($options,
            $database, '', $query, $limit, $errorStops, $onlyErrors);
        // $this->logger()->debug(\json_encode($queryResults));

        $content = '';
        foreach($queryResults['results'] as $results)
        {
            $content .= $this->render('sql/results', $results);
        }
        $this->response->html('adminer-command-results', $content);

        return $this->response;
    }

    /**
     * Display the file import form
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function showImportForm(string $server, string $database = '')
    {
        // De-activate the sidebar menu items
        // $this->jq('.list-group-item', '#'. $this->package->getDbMenuId())->removeClass('active');

        // $content = $this->render('sql/import');
        // $this->response->html($this->package->getDbContentId(), $content);
        $this->response->dialog->warning("This feature is not yet implemented.");

        return $this->response;
    }

    /**
     * Display the export form
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function showExportForm(string $server, string $database = '')
    {
        // De-activate the sidebar menu items
        // $this->jq('.list-group-item', '#'. $this->package->getDbMenuId())->removeClass('active');

        // $content = $this->render('sql/export');
        // $this->response->html($this->package->getDbContentId(), $content);
        $this->response->dialog->warning("This feature is not yet implemented.");

        return $this->response;
    }
}
