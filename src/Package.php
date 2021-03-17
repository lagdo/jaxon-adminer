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
    public function getDbContentId()
    {
        return 'adminer-database-content';
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
     * Get the default server to connect to
     *
     * @param array $servers
     *
     * @return string
     */
    private function getDefaultServer(array $servers)
    {
        // $default = $this->getConfig()->getOption('default', '');
        // if(\in_array($default, $servers))
        // {
        //     return $default;
        // }
        // if(\count($servers) > 0)
        // {
        //     return $servers[0];
        // }
        return '';
    }

    /**
     * Get the HTML tags to include javascript code and files into the page
     *
     * @return string
     */
    public function getScript()
    {
        return $this->view()->render('adminer::codes::script');
    }

    /**
     * Get the javascript code to execute after page load
     *
     * @return string
     */
    public function getReadyScript()
    {
        return '';
        // $servers = \array_keys($this->getConfig()->getOption('servers', []));
        // if(!($server = $this->getDefaultServer($servers)))
        // {
        //     return '';
        // }
        // return jaxon()->request(Server::class)->connect($server);
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

        return $this->view()->render('adminer::views::home', [
            'connect' => $connect,
            'servers' => $servers,
            'default' => $this->getConfig()->getOption('default', ''),
            'dbListId' => $this->getDbListId(),
            'dbMenuId' => $this->getDbMenuId(),
            'dbContentId' => $this->getDbContentId(),
        ]);
    }
}