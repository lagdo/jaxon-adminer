<?php

namespace Lagdo\DbAdmin\App;

use Lagdo\DbAdmin\CallableClass;

use Exception;

/**
 * Adminer Ajax client
 */
class Export extends CallableClass
{
    /**
     * Show the export form
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    protected function showForm(string $server, string $database)
    {
        $exportOptions = $this->dbAdmin->getExportOptions($server, $database);

        // Make data available to views
        $this->view()->shareValues($exportOptions);

        // Set main menu buttons
        $this->response->html($this->package->getMainActionsId(), $this->render('main/actions'));

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
     * Show the export form for a server
     *
     * @param string $server      The database server
     *
     * @return \Jaxon\Response\Response
     */
    public function showServerForm(string $server)
    {
        return $this->showForm($server, '');
    }

    /**
     * Show the export form for a database
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function showDatabaseForm(string $server, string $database = '')
    {
        return $this->showForm($server, $database);
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

        $results = $this->dbAdmin->exportDatabases($server, $databases, $tables, $formValues);
        // $this->logger()->debug('Form values', $formValues);

        $content = $this->render('sql/dump.sql', $results);
        // Dump file
        $output = $formValues['output'] ?? 'text';
        $extension = $output === 'gz' ? '.gz' : ($output === 'file' ? '.sql' : '.txt');
        if($output === 'gz')
        {
            // Zip content
            $content = \gzencode($content);
            if(!$content)
            {
                $this->response->dialog->error('Unable to gzip dump.', 'Error');
                return $this->response;
            }
        }
        $name = '/' . \uniqid() . $extension;
        $path = \rtrim($this->package->getOption('export.dir'), '/') . $name;
        if(!@\file_put_contents($path, $content))
        {
            $this->response->dialog->error('Unable to write dump to file.', 'Error');
            return $this->response;
        }

        $link = \rtrim($this->package->getOption('export.url'), '/') . $name;
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
