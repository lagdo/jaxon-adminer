<?php

namespace Lagdo\Adminer\Ajax;

use Lagdo\Adminer\Package;
use Lagdo\Adminer\AdminerCallable;
use Lagdo\Adminer\Db\Proxy as DbProxy;

use Exception;

/**
 * Adminer Ajax client
 */
class Table extends AdminerCallable
{
    /**
     * The Jaxon Adminer package
     *
     * @var Package
     */
    protected $package;

    /**
     * The proxy to Adminer functions
     *
     * @var DbProxy
     */
    protected $dbProxy;

    /**
     * The constructor
     *
     * @param Package $package    The Adminer package
     * @param DbProxy $dbProxy    The proxy to Adminer
     */
    public function __construct(Package $package, DbProxy $dbProxy)
    {
        $this->package = $package;
        $this->dbProxy = $dbProxy;
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

        $fieldsInfo = $this->dbProxy->getTableFields($options, $database, $table);
        // Make fields info available to views
        foreach($fieldsInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        $content = $this->render('main/table');
        $this->response->html($this->package->getDbContentId(), $content);

        $content = $this->render('main/content');
        $this->response->html("tab-content-fields", $content);

        // Show indexes
        $indexesInfo = $this->dbProxy->getTableIndexes($options, $database, $table);
        if(\is_array($indexesInfo))
        {
            // Make indexes info available to views
            foreach($indexesInfo as $name => $value)
            {
                $this->view()->share($name, $value);
            }

            $content = $this->render('main/content');
            $this->response->html("tab-content-indexes", $content);
        }

        // Show foreign keys
        $foreignKeysInfo = $this->dbProxy->getTableForeignKeys($options, $database, $table);
        if(\is_array($foreignKeysInfo))
        {
            // Make foreign keys info available to views
            foreach($foreignKeysInfo as $name => $value)
            {
                $this->view()->share($name, $value);
            }

            $content = $this->render('main/content');
            $this->response->html("tab-content-foreign-keys", $content);
        }

        // Show triggers
        $triggersInfo = $this->dbProxy->getTableTriggers($options, $database, $table);
        if(\is_array($triggersInfo))
        {
            // Make triggers info available to views
            foreach($triggersInfo as $name => $value)
            {
                $this->view()->share($name, $value);
            }

            $content = $this->render('main/content');
            $this->response->html("tab-content-triggers", $content);
        }

        return $this->response;
    }
}
