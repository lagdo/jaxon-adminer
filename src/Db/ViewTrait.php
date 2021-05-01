<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to view functions
 */
trait ViewTrait
{
    /**
     * The proxy
     *
     * @var ViewProxy
     */
    protected $viewProxy = null;

    /**
     * Get the proxy
     *
     * @return ViewProxy
     */
    protected function view()
    {
        return $this->viewProxy ?: ($this->viewProxy = new ViewProxy());
    }

    /**
     * Get details about a view or a view
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $view      The view name
     *
     * @return array
     */
    public function getViewInfo(string $server, string $database, string $view)
    {
        $options = $this->connect($server, $database);

        $this->setBreadcrumbs([$options['name'], $database, \adminer\lang('Views'), $view]);

        return $this->view()->getViewInfo($view);
    }

    /**
     * Get details about a view or a view
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $view      The view name
     *
     * @return array
     */
    public function getViewFields(string $server, string $database, string $view)
    {
        $this->connect($server, $database);
        return $this->view()->getViewFields($view);
    }

    /**
     * Get the triggers of a view
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $view      The view name
     *
     * @return array|null
     */
    public function getViewTriggers(string $server, string $database, string $view)
    {
        $this->connect($server, $database);
        return $this->view()->getViewTriggers($view);
    }
}
