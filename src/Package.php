<?php

namespace Lagdo\Adminer;

use Jaxon\Plugin\Package as JaxonPackage;
use Lagdo\Adminer\Ajax\Server;

/**
 * Adminer package
 */
class Package extends JaxonPackage
{
    /**
     * Get the div id of the HTML element
     *
     * @return string
     */
    public function getContainerId()
    {
        return 'adminer';
    }

    /**
     * Get the div id of the HTML element
     *
     * @return string
     */
    public function getUserInfoId()
    {
        return 'adminer-user-info';
    }

    /**
     * Get the div id of the HTML element
     *
     * @return string
     */
    public function getServerInfoId()
    {
        return 'adminer-server-info';
    }

    /**
     * Get the div id of the HTML element
     *
     * @return string
     */
    public function getBreadcrumbsId()
    {
        return 'adminer-breadcrumbs';
    }

    /**
     * Get the div id of the HTML element
     *
     * @return string
     */
    public function getMainActionsId()
    {
        return 'adminer-main-actions';
    }

    /**
     * Get the div id of the HTML element
     *
     * @return string
     */
    public function getServerActionsId()
    {
        return 'adminer-server-actions';
    }

    /**
     * Get the div id of the HTML element
     *
     * @return string
     */
    public function getDbListId()
    {
        return 'adminer-database-list';
    }

    /**
     * Get the div id of the HTML element
     *
     * @return string
     */
    public function getDbMenuId()
    {
        return 'adminer-database-menu';
    }

    /**
     * Get the div id of the HTML element
     *
     * @return string
     */
    public function getDbActionsId()
    {
        return 'adminer-database-actions';
    }

    /**
     * Get the div id of the HTML element
     *
     * @return string
     */
    public function getDbContentId()
    {
        return 'adminer-database-content';
    }

    /**
     * Get all the ids
     *
     * @return array
     */
    public function getIds()
    {
        return [
            'containerId' => $this->getContainerId(),
            'userInfoId' => $this->getUserInfoId(),
            'serverInfoId' => $this->getServerInfoId(),
            'breadcrumbsId' => $this->getBreadcrumbsId(),
            'mainActionsId' => $this->getMainActionsId(),
            'serverActionsId' => $this->getServerActionsId(),
            'dbListId' => $this->getDbListId(),
            'dbMenuId' => $this->getDbMenuId(),
            'dbActionsId' => $this->getDbActionsId(),
            'dbContentId' => $this->getDbContentId(),
        ];
    }

    /**
     * Get the path to the config file
     *
     * @return string
     */
    public static function getConfigFile()
    {
        return realpath(__DIR__ . '/../config/config.php');
    }

    /**
     * Get a given server options
     *
     * @param string $server    The server name in the configuration
     *
     * @return array
     */
    public function getServerOptions($server)
    {
        return $this->getConfig()->getOption("servers.$server", []);
    }

    /**
     * Get the value of a given package option
     *
     * @param string $option    The option name
     * @param mixed  $default   The default value
     *
     * @return mixed
     */
    public function getOption($option, $default = null)
    {
        return $this->getConfig()->getOption($option, $default);
    }

    /**
     * Get the default server to connect to
     *
     * @return string
     */
    private function getDefaultServer()
    {
        $servers = $this->getConfig()->getOption('servers', []);
        $default = $this->getConfig()->getOption('default', '');
        if(\array_key_exists($default, $servers))
        {
            return $default;
        }
        // if(\count($servers) > 0)
        // {
        //     return $servers[0];
        // }
        return '';
    }

    /**
     * Get the HTML tags to include CSS code and files into the page
     *
     * @return string
     */
    public function getCss()
    {
        return $this->view()->render('adminer::codes::styles', $this->getIds());
    }

    /**
     * Get the HTML tags to include javascript code and files into the page
     *
     * @return string
     */
    public function getScript()
    {
        return $this->view()->render('adminer::codes::script', $this->getIds());
    }

    /**
     * Get the javascript code to execute after page load
     *
     * @return string
     */
    public function getReadyScript()
    {
        if(!($server = $this->getDefaultServer()))
        {
            return '';
        }
        return jaxon()->request(Server::class)->connect($server);
    }

    /**
     * Get the HTML code of the package home page
     *
     * @return string
     */
    public function getHtml()
    {
        // Add an HTML container block for each server in the config file
        $servers = $this->getConfig()->getOption('servers', []);
        \array_walk($servers, function(&$server) {
            $server = $server['name'];
        });

        $connect = \jaxon()->request(Server::class)->connect(\pm()->select('adminer-dbhost-select'));

        return $this->view()->render('adminer::views::home', $this->getIds())
            ->with('connect', $connect)
            ->with('servers', $servers)
            ->with('default', $this->getConfig()->getOption('default', ''));
    }
}
