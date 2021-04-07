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
        $options = $this->package->getServerOptions($server);

        $importOptions = $this->dbProxy->getImportOptions($options, $database);

        // Make data available to views
        foreach($importOptions as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        // Update the breadcrumbs
        $this->showBreadcrumbs();

        // De-activate the sidebar menu items
        $this->jq('.list-group-item', '#'. $this->package->getDbMenuId())->removeClass('active');

        $content = $this->render('sql/import');
        $this->response->html($this->package->getDbContentId(), $content);
        $this->response->script("jaxon.adminer.setFileUpload('#adminer-import-sql-file-upload')");

        return $this->response;
    }
}
