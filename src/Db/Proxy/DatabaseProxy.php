<?php

namespace Lagdo\Adminer\Db\Proxy;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class DatabaseProxy
{
    /**
     * The final schema list
     *
     * @var array
     */
    protected $finalSchemas = null;

    /**
     * The schemas the user has access to
     *
     * @var array
     */
    protected $userSchemas = null;

    /**
     * The constructor
     *
     * @param array $options    The server config options
     */
    public function __construct(array $options)
    {
        // Set the user schemas, if defined.
        if(\array_key_exists('access', $options) &&
            \is_array($options['access']) &&
            \array_key_exists('schemas', $options['access']) &&
            \is_array($options['access']['schemas']))
        {
            $this->userSchemas = $options['access']['schemas'];
        }
    }

    /**
     * Get the schemas from the connected database
     *
     * @return array
     */
    protected function schemas()
    {
        global $adminer;
        // Get the schema lists
        if($this->finalSchemas === null)
        {
            $this->finalSchemas = $adminer->schemas();
            if(\is_array($this->userSchemas))
            {
                // Only keep schemas that appear in the config.
                $this->finalSchemas = \array_intersect($this->finalSchemas, $this->userSchemas);
                $this->finalSchemas = \array_values($this->finalSchemas);
            }
        }
        return $this->finalSchemas;
    }

    /**
     * Connect to a database server
     *
     * @return void
     */
    public function getDatabaseInfo()
    {
        $sql_actions = [
            'database-command' => \adminer\lang('SQL command'),
            'database-import' => \adminer\lang('Import'),
            'database-export' => \adminer\lang('Export'),
        ];

        $menu_actions = [
            'table' => \adminer\lang('Tables'),
            // 'view' => \adminer\lang('Views'),
            // 'routine' => \adminer\lang('Routines'),
            // 'sequence' => \adminer\lang('Sequences'),
            // 'type' => \adminer\lang('User types'),
            // 'event' => \adminer\lang('Events'),
        ];
        if(\adminer\support('view'))
        {
            $menu_actions['view'] = \adminer\lang('Views');
        }
        if(\adminer\support('routine'))
        {
            $menu_actions['routine'] = \adminer\lang('Routines');
        }
        if(\adminer\support('sequence'))
        {
            $menu_actions['sequence'] = \adminer\lang('Sequences');
        }
        if(\adminer\support('type'))
        {
            $menu_actions['type'] = \adminer\lang('User types');
        }
        if(\adminer\support('event'))
        {
            $menu_actions['event'] = \adminer\lang('Events');
        }

        // From db.inc.php
        $schemas = null;
        if(\adminer\support("scheme"))
        {
            $schemas = $this->schemas();
        }
        // $tables_list = \adminer\tables_list();

        // $tables = [];
        // foreach($table_status as $table)
        // {
        //     $tables[] = \adminer\h($table);
        // }

        return \compact('sql_actions', 'menu_actions', 'schemas'/*, 'tables'*/);
    }

    /**
     * Get the tables from a database server
     *
     * @return void
     */
    public function getTables()
    {
        global $adminer;

        $main_actions = [
            'add-table' => \adminer\lang('Create table'),
        ];

        $headers = [
            \adminer\lang('Table'),
            \adminer\lang('Engine'),
            \adminer\lang('Collation'),
            // \adminer\lang('Data Length'),
            // \adminer\lang('Index Length'),
            // \adminer\lang('Data Free'),
            // \adminer\lang('Auto Increment'),
            // \adminer\lang('Rows'),
            \adminer\lang('Comment'),
        ];

        // From db.inc.php
        // $table_status = \adminer\table_status('', true); // Tables details
        $table_status = \adminer\table_status(); // Tables details

        $details = [];
        foreach($table_status as $table => $status)
        {
            if(!\adminer\is_view($status))
            {
                $details[] = [
                    'name' => $adminer->tableName($status),
                    'engine' => \array_key_exists('Engine', $status) ? $status['Engine'] : '',
                    'collation' => '',
                    'comment' => \array_key_exists('Comment', $status) ? $status['Comment'] : '',
                ];
            }
        }

        $select = \adminer\lang('Select');
        return \compact('main_actions', 'headers', 'details', 'select');
    }

    /**
     * Get the views from a database server
     * Almost the same as getTables()
     *
     * @return void
     */
    public function getViews()
    {
        global $adminer;

        $main_actions = [
            'add-view' => \adminer\lang('Create view'),
        ];

        $headers = [
            \adminer\lang('View'),
            \adminer\lang('Engine'),
            // \adminer\lang('Data Length'),
            // \adminer\lang('Index Length'),
            // \adminer\lang('Data Free'),
            // \adminer\lang('Auto Increment'),
            // \adminer\lang('Rows'),
            \adminer\lang('Comment'),
        ];

        // From db.inc.php
        // $table_status = \adminer\table_status('', true); // Tables details
        $table_status = \adminer\table_status(); // Tables details

        $details = [];
        foreach($table_status as $table => $status)
        {
            if(\adminer\is_view($status))
            {
                $details[] = [
                    'name' => $adminer->tableName($status),
                    'engine' => \array_key_exists('Engine', $status) ? $status['Engine'] : '',
                    'comment' => \array_key_exists('Comment', $status) ? $status['Comment'] : '',
                ];
            }
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get the routines from a given database
     *
     * @return void
     */
    public function getRoutines()
    {
        $main_actions = [
            'procedure' => \adminer\lang('Create procedure'),
            'function' => \adminer\lang('Create function'),
        ];

        $headers = [
            \adminer\lang('Name'),
            \adminer\lang('Type'),
            \adminer\lang('Return type'),
        ];

        // From db.inc.php
        $routines = \adminer\support("routine") ? \adminer\routines() : [];
        $details = [];
        foreach($routines as $routine)
        {
            // not computed on the pages to be able to print the header first
            // $name = ($routine["SPECIFIC_NAME"] == $routine["ROUTINE_NAME"] ?
            //     "" : "&name=" . urlencode($routine["ROUTINE_NAME"]));

            $details[] = [
                'name' => \array_key_exists("ROUTINE_NAME", $routine) ?
                    \adminer\h($routine["ROUTINE_NAME"]) : '',
                'type' => \array_key_exists("ROUTINE_TYPE", $routine) ?
                    \adminer\h($routine["ROUTINE_TYPE"]) : '',
                'returnType' => \array_key_exists("DTD_IDENTIFIER", $routine) ?
                    \adminer\h($routine["DTD_IDENTIFIER"]) : '',
                // 'alter' => \adminer\lang('Alter'),
            ];
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get the routines from a given database
     *
     * @return void
     */
    public function getSequences()
    {
        $main_actions = [
            'sequence' => \adminer\lang('Create sequence'),
        ];

        $headers = [
            \adminer\lang('Name'),
        ];

        $sequences = [];
        if(\adminer\support("sequence"))
        {
            // From db.inc.php
            $sequences = \adminer\get_vals("SELECT sequence_name FROM information_schema.sequences ".
                "WHERE sequence_schema = current_schema() ORDER BY sequence_name");
        }
        $details = [];
        foreach($sequences as $sequence)
        {
            $details[] = [
                'name' => \adminer\h($sequence),
            ];
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get the routines from a given database
     *
     * @return array
     */
    public function getUserTypes()
    {
        $main_actions = [
            'type' => \adminer\lang('Create type'),
        ];

        $headers = [
            \adminer\lang('Name'),
        ];

        // From db.inc.php
        $userTypes = \adminer\support("type") ? \adminer\types() : [];
        $details = [];
        foreach($userTypes as $userType)
        {
            $details[] = [
                'name' => \adminer\h($userType),
            ];
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get the routines from a given database
     *
     * @return array
     */
    public function getEvents()
    {
        $main_actions = [
            'event' => \adminer\lang('Create event'),
        ];

        $headers = [
            \adminer\lang('Name'),
            \adminer\lang('Schedule'),
            \adminer\lang('Start'),
            // \adminer\lang('End'),
        ];

        // From db.inc.php
        $events = \adminer\support("event") ? \adminer\get_rows("SHOW EVENTS") : [];
        $details = [];
        foreach($events as $event)
        {
            $detail = [
                'name' => \adminer\h($event["Name"]),
            ];
            if(($event["Execute at"]))
            {
                $detail['schedule'] = \adminer\lang('At given time');
                $detail['start'] = $event["Execute at"];
                // $detail['end'] = '';
            }
            else
            {
                $detail['schedule'] = \adminer\lang('Every') . " " .
                    $event["Interval value"] . " " . $event["Interval field"];
                $detail['start'] = $event["Starts"];
                // $detail['end'] = '';
            }
            $details[] = $detail;
        }

        return \compact('main_actions', 'headers', 'details');
    }
}
