<?php

namespace Lagdo\Adminer\Ajax;

use Lagdo\Adminer\AdminerCallable;

use Exception;

/**
 * Adminer Ajax client
 */
class Export extends AdminerCallable
{
    /**
     * Display the SQL command form
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function showExportForm(string $server, string $database = '')
    {
        $exportOptions = $this->dbProxy->getExportOptions($server, $database);

        // Make data available to views
        foreach($exportOptions as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        // Update the breadcrumbs
        $this->showBreadcrumbs();

        // De-activate the sidebar menu items
        $this->jq('.list-group-item', '#'. $this->package->getDbMenuId())->removeClass('active');

        $btnId = 'adminer-main-command-execute';
        $formId = 'adminer-main-command-form';
        $queryId = 'adminer-main-command-query';

        $content = $this->render('sql/export', [
            'btnId' => $btnId,
            'formId' => $formId,
            'queryId' => $queryId,
            'defaultLimit' => 20,
        ]);
        $this->response->html($this->package->getDbContentId(), $content);
        $this->response->script("jaxon.adminer.selectAllCheckboxes('adminer-export-database')");
        $this->response->script("jaxon.adminer.selectAllCheckboxes('adminer-export-table-name')");
        $this->response->script("jaxon.adminer.selectAllCheckboxes('adminer-export-table-data')");

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

        $queryResults = $this->dbProxy->executeExport($server,
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
}
