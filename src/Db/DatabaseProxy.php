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
     * @return void
     */
    public function getTables()
    {
        global $adminer;

        $main_actions = [
            'database' => \adminer\lang('Alter database'),
            'c_scheme' => \adminer\lang('Create schema'),
            'a_scheme' => \adminer\lang('Alter schema'),
            'd_scheme' => \adminer\lang('Database schema'),
            'privileges' => \adminer\lang('Privileges'),
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
