<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class ServerProxy
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
        if($next_attempt > 0) { //! do the same with permanent login
            throw new Exception(lang('Too many unsuccessful logins, try again in %d minute(s).', ceil($next_attempt / 60)));
        }
    }

    /**
     * Connect to a database server
     *
     * @param array $options    The corresponding config options
     * @param string $db        The database name
     *
     * @return void
     */
    public function connect(array $options, string $db = '')
    {
        global $adminer, $host, $port, $connection, $driver;

        // Prevent multiple calls.
        if(($connection))
        {
            return;
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
        define("DB", $db); // for the sake of speed and size
        define("ME", '');
        // define("ME", preg_replace('~\?.*~', '', relative_uri()) . '?'
        //  . (sid() ? SID . '&' : '')
        //  . (DRIVER . "=" . urlencode($server) . '&')
        //  . ("username=" . urlencode($username) . '&')
        //  . ('db=' . urlencode(DB) . '&')
        // );

        // Run the authentication code, from auth.inc.php.
        set_password($vendor, $server, $username, $password);
        $_SESSION["db"][$vendor][$server][$username][$db] = true;
        if(preg_match('~^\s*([-+]?\d+)~', $port, $match) && ($match[1] < 1024 || $match[1] > 65535)) { // is_numeric('80#') would still connect to port 80
            throw new Exception(lang('Connecting to privileged ports is not allowed.'));
        }

        // $this->check_invalid_login();
        $adminer->credentials = ["$host:$port", $username, $password];
        $connection = connect();
        $driver = new \Min_Driver($connection);

        // From adminer.inc.php
        if(($db))
        {
            $connection->select_db($db);
        }
    }

    /**
     * Connect to a database server
     *
     * @param array $options    The corresponding config options
     *
     * @return void
     */
    public function getServerInfo(array $options)
    {
        global $adminer, $drivers, $connection;

        // Content from the connect_error() function in connect.inc.php
        $main_actions = [
            'databases' => \lang('Databases'),
            'database' => \lang('Create database'),
            'privileges' => \lang('Privileges'),
            'processlist' => \lang('Process list'),
            'variables' => \lang('Variables'),
            'status' => \lang('Status'),
        ];

        // Get the database lists
        // Passing false as parameter to this call prevent from using the slow_query() function,
        // which outputs data to the browser that are prepended to the Jaxon response.
        $databases = $adminer->databases(false);

        $messages = [
            \lang('%s version: %s through PHP extension %s', $drivers[DRIVER], "<b>" .
                \h($connection->server_info) . "</b>", "<b>$connection->extension</b>"),
            \lang('Logged as: %s', "<b>" . \h(\logged_user()) . "</b>"),
        ];

        $actions = [
            'host_sql_command' => \lang('SQL command'),
            'host_export' => \lang('Export'),
            'host_create_table' => \lang('Create table'),
        ];

        return \compact('databases', 'messages', 'main_actions', 'actions');
    }

    /**
     * Connect to a database server
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return void
     */
    public function getDatabaseInfo(array $options, string $database)
    {
        $actions = [
            'db_sql_command' => \lang('SQL command'),
            'db_import' => \lang('Import'),
            'db_export' => \lang('Export'),
            'db_create_table' => \lang('Create table'),
        ];

        $features = [
            'table' => true,
            'search' => \support('table'),
        ];
        $_features = [
            'comment',
            'indexes',
            'scheme',
            'copy',
            'view',
            'routine',
            'procedure',
            'sequence',
            'type',
            'event',
        ];
        foreach($_features as $feature)
        {
            $features[$feature] = \support($feature);
        }
        $menu_actions = [
            'table' => \lang('Tables and views'),
            // 'search' => \lang('Search data in tables'),
            'routine' => \lang('Routines'),
            'sequence' => \lang('Sequences'),
            'type' => \lang('User types'),
            'event' => \lang('Events'),
        ];

        // From db.inc.php
        // $tables_list = \tables_list();

        // $tables = [];
        // foreach($table_status as $table)
        // {
        //     $tables[] = \h($table);
        // }

        return \compact(/*'tables', */'actions', 'features', 'menu_actions');
    }

    /**
     * Get the database list
     *
     * @return void
     */
    public function getDatabases()
    {
        global $adminer;

        // Get the database lists
        // Passing false as parameter to this call prevent from using the slow_query() function,
        // which outputs data to the browser that are prepended to the Jaxon response.
        $databases = $adminer->databases(false);
        $tables = \count_tables($databases);

        $dbSupport = \support("database");
        $headers = [
            \lang('Database'),
            \lang('Collation'),
            \lang('Tables'),
            \lang('Size'),
        ];

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

        return \compact('headers', 'details');
    }

    /**
     * Get the processes
     *
     * @return array
     */
    public function getProcesses()
    {
        global $jush;
        // From processlist.inc.php
        $processes = \process_list();

        // From processlist.inc.php
        // TODO: Add a kill column in the headers
        $headers = [];
        $details = [];
        foreach($processes as $process)
        {
            // Set the keys of the first etry as headers
            if(\count($headers) === 0)
            {
                $headers = \array_keys($process);
            }
            $detail = [];
            foreach($process as $key => $val)
            {
                $detail[] =
                    ($jush == "sql" && $key == "Info" && \preg_match("~Query|Killed~", $process["Command"]) && $val != "") ||
                    ($jush == "pgsql" && $key == "current_query" && $val != "<IDLE>") ||
                    ($jush == "oracle" && $key == "sql_text" && $val != "") ?
                    "<code class='jush-$jush'>" . \shorten_utf8($val, 50) . "</code>" . lang('Clone')
                    : \h($val);
            }
            $details[] = $detail;
        }

        return \compact('headers', 'details');
    }

    /**
     * Get the variables
     *
     * @return array
     */
    public function getVariables()
    {
        // From variables.inc.php
        $variables = \show_variables();

        $headers = ['', ''];

        $details = [];
        // From variables.inc.php
        foreach($variables as $key => $val)
        {
            $details[] = [\h($key), \shorten_utf8($val, 50)];
        }

        return \compact('headers', 'details');
    }

    /**
     * Get the server status
     *
     * @return array|null
     */
    public function getStatus()
    {
        // From variables.inc.php
        $status = \show_status();

        $headers = ['', ''];

        $details = [];
        // From variables.inc.php
        foreach($status as $key => $val)
        {
            $details[] = [\h($key), \h($val)];
        }

        return \compact('headers', 'details');
    }
}
