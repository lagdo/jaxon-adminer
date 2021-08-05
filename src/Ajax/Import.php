<?php

namespace Lagdo\Adminer\Ajax;

use Lagdo\Adminer\AdminerCallable;

use Exception;

/**
 * Adminer Ajax client
 */
class Import extends AdminerCallable
{
    /**
     * Display the SQL command form
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function showImportForm(string $server, string $database = '')
    {
        $importOptions = $this->dbProxy->getImportOptions($server, $database);

        // Make data available to views
        foreach($importOptions as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        // Update the breadcrumbs
        $this->showBreadcrumbs();

        // De-activate the sidebar menu items
        $this->jq('.list-group-item', '#'. $this->package->getDbMenuId())->removeClass('active');

        $formId = 'adminer-import-form';
        $webFileBtnId = 'adminer-import-web-file-btn';
        $sqlFilesBtnId = 'adminer-import-sql-files-btn';
        $sqlChooseBtnId = 'adminer-import-choose-files-btn';
        $sqlFilesDivId = 'adminer-import-sql-files-wrapper';
        $sqlFilesInputId = 'adminer-import-sql-files-input';
        $content = $this->render('sql/import', \compact('formId', 'sqlFilesBtnId',
            'sqlChooseBtnId', 'webFileBtnId', 'sqlFilesDivId', 'sqlFilesInputId'));

        $this->response->html($this->package->getDbContentId(), $content);
        $this->response->script("jaxon.adminer.setFileUpload('#$sqlFilesDivId', '#$sqlChooseBtnId', '#$sqlFilesInputId')");

        $this->jq("#$webFileBtnId")->click($this->rq()->executeWebFile($server, $database));
        $this->jq("#$sqlFilesBtnId")
            ->click($this->rq()->executeSqlFiles($server, $database, \pm()->form($formId)));

        return $this->response;
    }

    /**
     * Run a webfile
     *
     * @param string $server      The database server
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function executeWebFile(string $server, string $database)
    {
        return $this->response;
    }

    /**
     * Run a webfile
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param array $formValues
     *
     * @return \Jaxon\Response\Response
     */
    public function executeSqlFiles(string $server, string $database, array $formValues)
    {
        $files = \array_map(function($file) {
            return $file->path();
        }, $this->files()['sql_files']);
        $errorStops = $formValues['error_stops'] ?? false;
        $onlyErrors = $formValues['only_errors'] ?? false;

        if(!$files)
        {
            $this->response->dialog->error('No file uploaded!', 'Error');
            return $this->response;
        }

        $queryResults = $this->dbProxy->executeSqlFiles($server,
            $files, $errorStops, $onlyErrors, $database);
        // $this->logger()->debug(\json_encode($queryResults));

        $content = $this->render('sql/results', $queryResults);
        $this->response->html('adminer-command-results', $content);

        return $this->response;
    }
}
