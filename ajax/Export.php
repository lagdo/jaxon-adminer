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

        $btnId = 'adminer-main-export-submit';
        $formId = 'adminer-main-export-form';
        $databaseNameId = 'adminer-export-database-name';
        $databaseDataId = 'adminer-export-database-data';
        $tableNameId = 'adminer-export-table-name';
        $tableDataId = 'adminer-export-table-data';

        $content = $this->render('sql/export', [
            'btnId' => $btnId,
            'formId' => $formId,
            'databaseNameId' => $databaseNameId,
            'databaseDataId' => $databaseDataId,
            'tableNameId' => $tableNameId,
            'tableDataId' => $tableDataId,
        ]);
        $this->response->html($this->package->getDbContentId(), $content);

        if(($database))
        {
            $this->response->script("jaxon.adminer.selectAllCheckboxes('$tableNameId')");
            $this->response->script("jaxon.adminer.selectAllCheckboxes('$tableDataId')");
            $this->jq("#$btnId")
                 ->click($this->rq()->exportOne($server, $database, \pm()->form($formId)));
            return $this->response;
        }

        $this->response->script("jaxon.adminer.selectAllCheckboxes('$databaseNameId')");
        $this->response->script("jaxon.adminer.selectAllCheckboxes('$databaseDataId')");
        $this->jq("#$btnId")
             ->click($this->rq()->exportSet($server, \pm()->form($formId)));
        return $this->response;
    }

    /**
     * Execute an SQL query and display the results
     *
     * @param string $server        The database server
     * @param array  $databases     The databases to dump
     * @param array  $tables        The tables to dump
     * @param array  $formValues
     *
     * @return \Jaxon\Response\Response
     */
    protected function export(string $server, array $databases, array $tables, array $formValues)
    {
        // Convert checkbox values to boolean
        $formValues['routines'] = \array_key_exists('routines', $formValues);
        $formValues['events'] = \array_key_exists('events', $formValues);
        $formValues['auto_increment'] = \array_key_exists('auto_increment', $formValues);
        $formValues['triggers'] = \array_key_exists('triggers', $formValues);

        $results = $this->dbProxy->exportDatabases($server, $databases, $tables, $formValues);
        // $this->logger()->debug('Form values', $formValues);

        $content = $this->render('sql/dump.sql', $results);
        // Dump file
        $name = '/' . \uniqid() . '.txt';
        $path = \rtrim(\jaxon()->getOption('adminer.export.dir'), '/') . $name;
        if(!@\file_put_contents($path, $content))
        {
            $this->response->dialog->error('Unable to write dump to file ' . $path, 'Error');
            return $this->response;
        }

        $link = \rtrim(\jaxon()->getOption('adminer.export.url'), '/') . $name;
        $this->response->script("window.open('$link', '_blank').focus()");
        return $this->response;
    }

    /**
     * Export a set of databases on a server
     *
     * @param string $server      The database server
     * @param array $formValues
     *
     * @return \Jaxon\Response\Response
     */
    public function exportSet(string $server, array $formValues)
    {
        $databases = [
            'list' => $formValues['database_list'] ?? [],
            'data' => $formValues['database_data'] ?? [],
        ];
        $tables = [
            'list' => '*',
            'data' => [],
        ];
        // $this->logger()->debug('exportServer', \compact('databases', 'tables'));

        return $this->export($server, $databases, $tables, $formValues);
    }

    /**
     * Export one database on a server
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param array $formValues
     *
     * @return \Jaxon\Response\Response
     */
    public function exportOne(string $server, string $database, array $formValues)
    {
        $databases = [
            'list' => [$database],
            'data' => [],
        ];
        $tables = [
            'list' => $formValues['table_list'] ?? [],
            'data' => $formValues['table_data'] ?? [],
        ];
        // $this->logger()->debug('exportDatabase', \compact('databases', 'tables'));

        return $this->export($server, $databases, $tables, $formValues);
    }
}
