<?php

namespace Lagdo\DbAdmin;

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
     * The facade to database functions
     *
     * @var DbAdmin
     */
    protected $dbAdmin;

    /**
     * The constructor
     *
     * @param Package $package    The Adminer package
     * @param DbAdmin $dbAdmin    The facade to database functions
     */
    public function __construct(Package $package, DbAdmin $dbAdmin)
    {
        $this->package = $package;
        $this->dbAdmin = $dbAdmin;
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
            'breadcrumbs' => $this->dbAdmin->getBreadcrumbs(),
        ]);
        $this->response->html($this->package->getBreadcrumbsId(), $content);
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
