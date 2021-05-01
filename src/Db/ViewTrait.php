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
     * Get details about a view
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
     * Get details about a view
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

    /**
     * Get a view
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $view      The view name
     *
     * @return array
     */
    public function getView(string $server, string $database, string $view)
    {
        $this->connect($server, $database);
        return $this->view()->getView($view);
    }

    /**
     * Create a view
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param array  $values    The view values
     *
     * @return array
     */
    public function createView(string $server, string $database, array $values)
    {
        $this->connect($server, $database);
        return $this->view()->createView($values);
    }

    /**
     * Update a view
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $view      The view name
     * @param array  $values    The view values
     *
     * @return array
     */
    public function updateView(string $server, string $database, string $view, array $values)
    {
        $this->connect($server, $database);
        return $this->view()->updateView($view, $values);
    }

    /**
     * Drop a view
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $view      The view name
     *
     * @return array
     */
    public function dropView(string $server, string $database, string $view)
    {
        $this->connect($server, $database);
        return $this->view()->dropView($view);
    }
}
