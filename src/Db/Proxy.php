<?php

namespace Lagdo\Adminer\Db;

use Jaxon\Plugin\Package as JaxonPackage;
use Lagdo\Adminer\Ajax\Server;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class Proxy
{
    /**
     * Copied from auth.inc.php
     *
     * @return void
     */
    protected function check_invalid_login() {
        global $adminer;
        $invalids = unserialize(@file_get_contents(get_temp_dir() . "/adminer.invalid")); // @ - may not exist
        $invalid = ($invalids ? $invalids[$adminer->bruteForceKey()] : []);
        $next_attempt = ($invalid[1] > 29 ? $invalid[0] - time() : 0); // allow 30 invalid attempts
        if ($next_attempt > 0) { //! do the same with permanent login
            throw new Exception(lang('Too many unsuccessful logins, try again in %d minute(s).', ceil($next_attempt / 60)));
        }
    }

    /**
     * Connect to a database server
     *
     * @param array $options    The corresponding config options
     *
     * @return void
     */
    protected function connect(array $options)
    {
        global $adminer, $host, $port, $connection, $driver;

        // Fixes
        define("SID", \session_id());

        $host = $options['host'];
        $port = $options['port'];
        $username = $options["username"];
        $password = $options["password"];
        $db = ''; // $options["db"];

        // Simulate a actual request to Adminer
        $vendor = $options['type'];
        $server = $host;
        $_GET[$vendor] = $server;
        $_GET['username'] = $username;

        // Load the adminer code, and discard the outputs
        \ob_start();
        include __DIR__ . '/../../adminer/jaxon.php';
        \ob_end_clean();

        // From bootstrap.inc.php
        define("SERVER", $server); // read from pgsql=localhost
        define("DB", $db); // for the sake of speed and size
        define("ME", preg_replace('~\?.*~', '', relative_uri()) . '?'
         . (sid() ? SID . '&' : '')
         . (DRIVER . "=" . urlencode($server) . '&')
         . ("username=" . urlencode($username) . '&')
         // . ('db=' . urlencode(DB) . '&')
        );

        // Run the authentication code, from auth.inc.php.
        set_password($vendor, $server, $username, $password);
        $_SESSION["db"][$vendor][$server][$username][$db] = true;
        if (preg_match('~^\s*([-+]?\d+)~', $port, $match) && ($match[1] < 1024 || $match[1] > 65535)) { // is_numeric('80#') would still connect to port 80
            throw new Exception(lang('Connecting to privileged ports is not allowed.'));
        }

        // $this->check_invalid_login();
        $adminer->credentials = ["$host:$port", $username, $password];
        $connection = connect();
        $driver = new \Min_Driver($connection);
    }

    /**
     * Connect to a database server
     *
     * @param array $options    The corresponding config options
     *
     * @return void
     */
    public function getServerData(array $options)
    {
        global $adminer, $drivers, $connection;
        $this->connect($options);

        // Get the database lists
        $databases = $adminer->databases();

        $messages = [
            lang('%s version: %s through PHP extension %s', $drivers[DRIVER], "<b>" .
                h($connection->server_info) . "</b>", "<b>$connection->extension</b>"),
            lang('Logged as: %s', "<b>" . h(logged_user()) . "</b>"),
        ];

        // Content from the connect_error() function in connect.inc.php
        $actions = [
			'database' => lang('Create database'),
			'privileges' => lang('Privileges'),
			'processlist' => lang('Process list'),
			'variables' => lang('Variables'),
			'status' => lang('Status'),
        ];

        $tables = \count_tables($databases);

        $dbSupport = support("database");
        $headers = [lang('Database'), lang('Collation'), lang('Tables'), lang('Size')];

        $collations = \collations();
        $details = [];
        foreach($databases as $database)
        {
            $details[] = [
                'name' => \h($database),
                'collation' => \h(\db_collation($database, $collations)),
                'tables' => $tables[$database],
                'size' => \db_size($database),
            ];
        }

        return \compact('databases', 'messages', 'actions', 'headers', 'details');
    }
}
