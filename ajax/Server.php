<?php

namespace Lagdo\Adminer\Ajax;

use Lagdo\Adminer\Package;
use Lagdo\Adminer\Db\Proxy as DbProxy;

use Jaxon\CallableClass;
use Exception;

/**
 * Adminer Ajax
 */
class Server extends CallableClass
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
     * Connect to a db server.
     * The database list will be displaye in the select component.
     *
     * @return \Jaxon\Response\Response
     */
    public function connect($server)
    {
        $options = $this->package->getServerOptions($server);

        $serverData = $this->dbProxy->getServerData($options);
        // Make server data available to views
        foreach($serverData as $name => $value)
        {
            $this->view()->share($name, $value);
        }

        $template = 'adminer::views::databases';
        $content = $this->view()->render($template, [
            'select' => $this->rq()->select($server, \pm()->select('adminer-dbname-select')),
        ]);
        $this->response->html($this->package->getDbListId(), $content);

        $template = 'adminer::views::server';
        $content = $this->view()->render($template);
        $this->response->html($this->package->getDbContentId(), $content);

        return $this->response;
    }

    /**
     * Select a database
     *
     * @param string $database    The database name
     *
     * @return \Jaxon\Response\Response
     */
    public function select($server, $database)
    {
        $serverOptions = $this->package->getServerOptions($server);

        try
        {
        }
        catch(Exception $e)
        {
            $this->response->dialog->error("Unable", 'Error');
            return $this->response;
        }
    }
}
