<?php

namespace Lagdo\Adminer\Db;

use Lagdo\Adminer\Package;
use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class Proxy
{
    use ServerTrait;
    use UserTrait;
    use DatabaseTrait;
    use TableTrait;
    use TableSelectTrait;
    use TableQueryTrait;
    use ViewTrait;
    use CommandTrait;
    use ExportTrait;
    use ImportTrait;

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
     * Copied from auth.inc.php
     *
     * @return void
     */
    // protected function check_invalid_login() {
    //     global $adminer;
    //     $invalids = \unserialize(@\file_get_contents(\adminer\get_temp_dir() . "/adminer.invalid")); // @ - may not exist
    //     $invalid = ($invalids ? $invalids[$adminer->bruteForceKey()] : []);
    //     $next_attempt = ($invalid[1] > 29 ? $invalid[0] - time() : 0); // allow 30 invalid attempts
    //     if($next_attempt > 0) { //! do the same with permanent login
    //         throw new Exception(\adminer\lang('Too many unsuccessful logins, try again in %d minute(s).', ceil($next_attempt / 60)));
    //     }
    // }

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
        global $connection;

        if($database !== '')
        {
            $connection->select_db($database);
            if($schema !== '')
            {
                \adminer\set_schema($schema, $connection);
            }
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
        global $adminer, $host, $port, $connection, $driver;

        $options = $this->package->getServerOptions($server);
        // Prevent multiple calls.
        if(($connection))
        {
            // From adminer.inc.php
            $this->select($database, $schema);
            return $options;
        }

        // Fixes
        define("SID", \session_id());

        $host = $options['host'];
        $port = $options['port'];
        $username = $options["username"];
        $password = $options["password"];

        // Simulate an actual request to Adminer
        $vendor = $options['driver'];
        $server = $host;
        $_GET[$vendor] = $server;
        $_GET['username'] = $username;

        // Load the adminer code, and discard the outputs
        \ob_start();
        include __DIR__ . '/../../adminer/jaxon.php';
        \ob_end_clean();

        // From bootstrap.inc.php
        define("SERVER", $server); // read from pgsql=localhost
        define("DB", $database); // for the sake of speed and size
        define("ME", '');
        // define("ME", preg_replace('~\?.*~', '', relative_uri()) . '?'
        //  . (sid() ? SID . '&' : '')
        //  . (DRIVER . "=" . urlencode($server) . '&')
        //  . ("username=" . urlencode($username) . '&')
        //  . ('db=' . urlencode(DB) . '&')
        // );

        // Run the authentication code, from auth.inc.php.
        // \adminer\set_password($vendor, $server, $username, $password);
        // $_SESSION["db"][$vendor][$server][$username][$database] = true;
        if(preg_match('~^\s*([-+]?\d+)~', $port, $match) && ($match[1] < 1024 || $match[1] > 65535)) {
            // is_numeric('80#') would still connect to port 80
            throw new Exception(\adminer\lang('Connecting to privileged ports is not allowed.'));
        }

        // $this->check_invalid_login();
        $adminer->credentials = ["$host:$port", $username, $password];
        $connection = \adminer\connect();
        $driver = new \adminer\Min_Driver($connection);

        // From adminer.inc.php
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
        return \adminer\support($feature);
    }
}
