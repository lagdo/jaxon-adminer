<?php

namespace Lagdo\Adminer;

use Jaxon\CallableClass;
use Jaxon\Utils\View\Store;

/**
 * Callable base class
 */
class AdminerCallable extends CallableClass
{
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
}
