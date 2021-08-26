<?php

namespace Lagdo\Adminer;

use Lagdo\Adminer\Package;
use Lagdo\Adminer\Db\AdminerDb;
use Lagdo\Adminer\Db\AdminerUtil;

use Exception;

/**
 * Facade to calls to the database functions
 */
class DbAdmin extends Facade\AbstractFacade
{
    use Facade\ServerTrait;
    use Facade\UserTrait;
    use Facade\DatabaseTrait;
    use Facade\TableTrait;
    use Facade\TableSelectTrait;
    use Facade\TableQueryTrait;
    use Facade\ViewTrait;
    use Facade\CommandTrait;
    use Facade\ExportTrait;
    use Facade\ImportTrait;

    /**
     * The breadcrumbs items
     *
     * @var array
     */
    protected $breadcrumbs = [];

    /**
     * The Jaxon Adminer package
     *
     * @var Package
     */
    protected $package;

    /**
     * The constructor
     *
     * @param Package $package    The Adminer package
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
    }

    /**
     * Get the breadcrumbs items
     *
     * @return array
     */
    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }

    /**
     * Set the breadcrumbs items
     *
     * @param array $breadcrumbs
     *
     * @return void
     */
    protected function setBreadcrumbs(array $breadcrumbs)
    {
        $this->breadcrumbs = $breadcrumbs;
    }

    /**
     * Select the database and schema
     *
     * @param string $database  The database name
     * @param string $schema    The database schema
     *
     * @return array
     */
    protected function select(string $database, string $schema)
    {
        if ($database !== '') {
            $this->db->selectDatabase($database, $schema);
        }
    }

    /**
     * Connect to a database server
     *
     * @param string $server    The selected server
     * @param string $database  The database name
     * @param string $schema    The database schema
     *
     * @return array
     */
    protected function connect(string $server, string $database = '', string $schema = '')
    {
        $options = $this->package->getServerOptions($server);
        // Prevent multiple calls.
        if (($this->db)) {
            $this->select($database, $schema);
            return $options;
        }

        // The Adminer constructor connects to the database.
        $this->db = new AdminerDb($options);
        $this->util = new AdminerUtil($this->db);
        $this->db->connect($this->util, $options['driver']);

        $this->select($database, $schema);
        return $options;
    }

    /**
     * Check if a database server supports a given feature
     *
     * @param string $server    The selected server
     * @param string $feature   The feature to check
     *
     * @return bool
     */
    public function support(string $server, string $feature)
    {
        $this->connect($server);
        return $this->db->support($feature);
    }
}
