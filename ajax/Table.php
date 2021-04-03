<?php

namespace Lagdo\Adminer\Ajax;

use Lagdo\Adminer\AdminerCallable;

use Exception;

/**
 * Adminer Ajax client
 */
class Table extends AdminerCallable
{
    /**
     * Display the content of a tab
     *
     * @param array  $viewData  The data to be displayed in the view
     * @param string $tabId     The tab container id
     *
     * @return void
     */
    protected function showTab(array $viewData, string $tabId)
    {
        // Make data available to views
        foreach($viewData as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        $content = $this->render('main/content');
        $this->response->html($tabId, $content);
    }

    /**
     * Show detailed info of a given table
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $table       The table name
     *
     * @return \Jaxon\Response\Response
     */
    public function showTable($server, $database, $table)
    {
        // $this->response->dialog->info("server $server, db $database, table $table", "Info");

        $options = $this->package->getServerOptions($server);

        $tableInfo = $this->dbProxy->getTableInfo($options, $database, $table);
        // Make table info available to views
        foreach($tableInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        // Update the breadcrumbs
        $this->showBreadcrumbs();

        $content = $this->render('main/db-table');
        $this->response->html($this->package->getDbContentId(), $content);

        // Show fields
        $fieldsInfo = $this->dbProxy->getTableFields($options, $database, $table);
        $this->showTab($fieldsInfo, 'tab-content-fields');

        // Show indexes
        $indexesInfo = $this->dbProxy->getTableIndexes($options, $database, $table);
        if(\is_array($indexesInfo))
        {
            $this->showTab($indexesInfo, 'tab-content-indexes');
        }

        // Show foreign keys
        $foreignKeysInfo = $this->dbProxy->getTableForeignKeys($options, $database, $table);
        if(\is_array($foreignKeysInfo))
        {
            $this->showTab($foreignKeysInfo, 'tab-content-foreign-keys');
        }

        // Show triggers
        $triggersInfo = $this->dbProxy->getTableTriggers($options, $database, $table);
        if(\is_array($triggersInfo))
        {
            $this->showTab($triggersInfo, 'tab-content-triggers');
        }

        return $this->response;
    }
}
