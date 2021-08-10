<?php

namespace Lagdo\Adminer;

use Jaxon\CallableClass as JaxonCallableClass;
use Jaxon\Utils\View\Store;

/**
 * Callable base class
 */
class CallableClass extends JaxonCallableClass
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
    public function __construct(Package $package, Db\Proxy $dbProxy)
    {
        $this->package = $package;
        $this->dbProxy = $dbProxy;
    }

    /**
     * Render a view
     *
     * @param string        $sViewName        The view name
     * @param array         $aViewData        The view data
     *
     * @return null|Store   A store populated with the view data
     */
    protected function render($sViewName, array $aViewData = [])
    {
        return $this->view()->render('adminer::views::' . $sViewName, $aViewData);
    }

    /**
     * Show breadcrumbs
     *
     * @return void
     */
    protected function showBreadcrumbs()
    {
        $content = $this->render('main/breadcrumbs', [
            'breadcrumbs' => $this->dbProxy->getBreadcrumbs(),
        ]);
        $this->response->html($this->package->getBreadcrumbsId(), $content);
        $this->response->html($this->package->getMainActionsId(), $this->render('main/actions'));
    }

    /**
     * Check if the user has access to a server
     *
     * @param string $server      The database server
     * @param boolean $showError  Show error message
     *
     * return bool
     */
    protected function checkServerAccess(string $server, $showError = true)
    {
        $serverAccess = $this->package->getConfig()->getOption("servers.$server.access.server", null);
        if($serverAccess === true ||
            ($serverAccess === null && $this->package->getConfig()->getOption('access.server', true)))
        {
            return true;
        }
        if($showError)
        {
            $this->response->dialog->warning('Access to server data is forbidden');
        }
        return false;
    }

    /**
     * Select a menu item
     *
     * @param string $menuId      The selected menu id
     * @param string $wrapperId   The menu item wrapper id
     *
     * return void
     */
    protected function selectMenuItem(string $menuId, string $wrapperId)
    {
        $this->jq('.adminer-menu-item', '#'. $this->package->getServerActionsId())->removeClass('active');
        $this->jq('.adminer-menu-item', '#'. $this->package->getDbActionsId())->removeClass('active');
        $this->jq('.adminer-menu-item', '#'. $this->package->getDbMenuId())->removeClass('active');
        $this->jq($menuId, '#'. $wrapperId)->addClass('active');
    }
}
