<?php

namespace Lagdo\Adminer;

use Lagdo\Adminer\Db\Db;
use Lagdo\Adminer\Db\Util;

use Exception;

/**
 * Admin to calls to the database functions
 */
class DbAdmin extends DbAdmin\AbstractAdmin
{
    use DbAdmin\ServerTrait;
    use DbAdmin\UserTrait;
    use DbAdmin\DatabaseTrait;
    use DbAdmin\TableTrait;
    use DbAdmin\TableSelectTrait;
    use DbAdmin\TableQueryTrait;
    use DbAdmin\ViewTrait;
    use DbAdmin\CommandTrait;
    use DbAdmin\ExportTrait;
    use DbAdmin\ImportTrait;

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
     * @var Translator
     */
    protected $translator;

    /**
     * The constructor
     *
     * @param Package $package    The Adminer package
     */
    public function __construct(Package $package)
    {
        $this->package = $package;

        $jaxon = \jaxon();
        $this->translator = $jaxon->di()->get(Translator::class);
        // Make the translator available into views
        $jaxon->view()->share('trans', $this->translator);
    }

    /**
     * Get a translated string
     * The first parameter is mandatory. Optional parameters can follow.
     *
     * @param string
     *
     * @return string
     */
    public function lang($idf)
    {
        return \call_user_func_array([$this->translator, "lang"], \func_get_args());
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
        $this->db->database = $database;
        $this->db->schema = $schema;
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
        // Prevent multiple calls.
        if (($this->db)) {
            $this->select($database, $schema);
            return;
        }

        $di = \jaxon()->di();
        $di->set('adminer_config_server', function() use($server) {
            return $server;
        });
        $this->db = $di->get(Db::class);
        $this->util = $di->get(Util::class);

        // Connect to the selected server
        $this->db->connect();
        $this->select($database, $schema);
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
