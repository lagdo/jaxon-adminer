<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class DatabaseProxy
{
    /**
     * Get the tables from a database server
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return void
     */
    public function getTables(array $options, string $database)
    {
        global $adminer;

        $main_actions = [
            'database' => \lang('Alter database'),
            'c_scheme' => \lang('Create schema'),
            'a_scheme' => \lang('Alter schema'),
            'd_scheme' => \lang('Database schema'),
            'privileges' => \lang('Privileges'),
        ];

        $headers = [
            \lang('Table'),
            \lang('Engine'),
            \lang('Collation'),
            // \lang('Data Length'),
            // \lang('Index Length'),
            // \lang('Data Free'),
            // \lang('Auto Increment'),
            // \lang('Rows'),
            \lang('Comment'),
        ];

        // From db.inc.php
        // $table_status = \table_status('', true); // Tables details
        $table_status = \table_status(); // Tables details

        $details = [];
        foreach($table_status as $table => $status)
        {
            $details[] = [
                'name' => $adminer->tableName($status),
                'engine' => \array_key_exists('Engine', $status) ? $status['Engine'] : '',
                'collation' => '',
                'comment' => \array_key_exists('Comment', $status) ? $status['Comment'] : '',
            ];
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get the routines from a given database
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return void
     */
    public function getRoutines(array $options, string $database)
    {
        $main_actions = [
            'procedure' => \lang('Create procedure'),
            'function' => \lang('Create function'),
        ];

        $headers = [
            \lang('Name'),
            \lang('Type'),
            \lang('Return type'),
        ];

        // From db.inc.php
        $routines = \support("routine") ? \routines() : [];
        $details = [];
        foreach($routines as $routine)
        {
            // not computed on the pages to be able to print the header first
            // $name = ($routine["SPECIFIC_NAME"] == $routine["ROUTINE_NAME"] ?
            //     "" : "&name=" . urlencode($routine["ROUTINE_NAME"]));

            $details[] = [
                'name' => \h($routine["ROUTINE_NAME"]),
                'type' => \h($routine["ROUTINE_TYPE"]),
                'returnType' => \h($routine["DTD_IDENTIFIER"]),
                // 'alter' => \lang('Alter'),
            ];
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get the routines from a given database
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return void
     */
    public function getSequences(array $options, string $database)
    {
        $main_actions = [
            'sequence' => \lang('Create sequence'),
        ];

        $headers = [
            \lang('Name'),
        ];

        $sequences = [];
        if(\support("sequence"))
        {
            // From db.inc.php
            $sequences = \get_vals("SELECT sequence_name FROM information_schema.sequences ".
                "WHERE sequence_schema = current_schema() ORDER BY sequence_name");
        }
        $details = [];
        foreach($sequences as $sequence)
        {
            $details[] = [
                'name' => \h($sequence),
            ];
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get the routines from a given database
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return void
     */
    public function getUserTypes(array $options, string $database)
    {
        $main_actions = [
            'type' => \lang('Create type'),
        ];

        $headers = [
            \lang('Name'),
        ];

        // From db.inc.php
        $userTypes = \support("type") ? \types() : [];
        $details = [];
        foreach($userTypes as $userType)
        {
            $details[] = [
                'name' => \h($userType),
            ];
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get the routines from a given database
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     *
     * @return void
     */
    public function getEvents(array $options, string $database)
    {
        $main_actions = [
            'event' => \lang('Create event'),
        ];

        $headers = [
            \lang('Name'),
            \lang('Schedule'),
            \lang('Start'),
            // \lang('End'),
        ];

        // From db.inc.php
        $events = \support("event") ? \get_rows("SHOW EVENTS") : [];
        $details = [];
        foreach($events as $event)
        {
            $detail = [
                'name' => \h($event["Name"]),
            ];
            if(($event["Execute at"]))
            {
                $detail['schedule'] = \lang('At given time');
                $detail['start'] = $event["Execute at"];
                // $detail['end'] = '';
            }
            else
            {
                $detail['schedule'] = \lang('Every') . " " . $event["Interval value"] . " " . $event["Interval field"];
                $detail['start'] = $event["Starts"];
                // $detail['end'] = '';
            }
            $details[] = $detail;
        }

        return \compact('main_actions', 'headers', 'details');
    }
}
