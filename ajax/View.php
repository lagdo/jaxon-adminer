<?php

namespace Lagdo\Adminer\Ajax;

use Lagdo\Adminer\AdminerCallable;

use Exception;

/**
 * Adminer Ajax client
 */
class View extends AdminerCallable
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
        $this->view()->shareValues($viewData);

        $content = $this->render('main/content');
        $this->response->html($tabId, $content);
    }

    /**
     * Show detailed info of a given view
     *
     * @param string $server      The database server
     * @param string $database    The database name
     * @param string $view        The view name
     *
     * @return \Jaxon\Response\Response
     */
    public function show($server, $database, $view)
    {
        $viewInfo = $this->dbProxy->getViewInfo($server, $database, $view);
        // Make view info available to views
        foreach($viewInfo as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        // Update the breadcrumbs
        $this->showBreadcrumbs();

        $content = $this->render('main/db-table');
        $this->response->html($this->package->getDbContentId(), $content);

        // Show fields
        $fieldsInfo = $this->dbProxy->getViewFields($server, $database, $view);
        $this->showTab($fieldsInfo, 'tab-content-fields');

        // Show triggers
        $triggersInfo = $this->dbProxy->getViewTriggers($server, $database, $view);
        if(\is_array($triggersInfo))
        {
            $this->showTab($triggersInfo, 'tab-content-triggers');
        }

        return $this->response;
    }
}
