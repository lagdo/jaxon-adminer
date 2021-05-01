<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class ViewProxy
{
    /**
     * The current table status
     *
     * @var mixed
     */
    protected $viewStatus = null;

    /**
     * Get the current table status
     *
     * @param string $table
     *
     * @return mixed
     */
    protected function status(string $table)
    {
        if(!$this->viewStatus)
        {
            $this->viewStatus = \adminer\table_status1($table, true);
        }
        return $this->viewStatus;
    }

    /**
     * Print links after select heading
     * Copied from selectLinks() in adminer.inc.php
     *
     * @param string new item options, NULL for no new item
     *
     * @return array
     */
    protected function getViewLinks($set = null)
    {
        global $jush, $driver;

        $links = [
            "select" => \adminer\lang('Select data'),
        ];
        if(\adminer\support("table") || \adminer\support("indexes"))
        {
            $links["table"] = \adminer\lang('Show structure');
        }
        if(\adminer\support("table"))
        {
            $links["alter"] = \adminer\lang('Alter view');
        }
        if($set !== null)
        {
            $links["edit"] = \adminer\lang('New item');
        }
        // $links['docs'] = \doc_link([$jush => $driver->tableHelp($name)], "?");

        return $links;
    }

    /**
     * Get details about a view
     *
     * @param string $table     The table name
     *
     * @return array
     */
    public function getViewInfo(string $table)
    {
        global $adminer;

        // From table.inc.php
        $status = $this->status($table);
        $name = $adminer->tableName($status);
        $title = ($status['Engine'] == 'materialized view' ?
            \adminer\lang('Materialized view') : \adminer\lang('View')) .
            ": " . ($name != "" ? $name : \adminer\h($table));

        $comment = $status["Comment"] ?? '';

        $tabs = [
            'fields' => \adminer\lang('Columns'),
            // 'indexes' => \adminer\lang('Indexes'),
            // 'foreign-keys' => \adminer\lang('Foreign keys'),
            // 'triggers' => \adminer\lang('Triggers'),
        ];
        if(\adminer\support("view_trigger"))
        {
            $tabs['triggers'] = \adminer\lang('Triggers');
        }

        return \compact('title', 'comment', 'tabs');
    }

    /**
     * Get the fields of a table or a view
     *
     * @param string $table     The table name
     *
     * @return array
     */
    public function getViewFields(string $table)
    {
        // From table.inc.php
        $fields = \adminer\fields($table);
        if(!$fields)
        {
            throw new Exception(\adminer\error());
        }

        $main_actions = $this->getViewLinks();

        $tabs = [
            'fields' => \adminer\lang('Columns'),
            // 'triggers' => \adminer\lang('Triggers'),
        ];
        if(\adminer\support("view_trigger"))
        {
            $tabs['triggers'] = \adminer\lang('Triggers');
        }

        $headers = [
            \adminer\lang('Name'),
            \adminer\lang('Type'),
            \adminer\lang('Collation'),
        ];
        $hasComment = \adminer\support('comment');
        if($hasComment)
        {
            $headers[] = \adminer\lang('Comment');
        }

        $details = [];
        foreach($fields as $field)
        {
            $type = \adminer\h($field["full_type"]);
            if($field["null"])
            {
                $type .= " <i>nullable</i>"; // " <i>NULL</i>";
            }
            if($field["auto_increment"])
            {
                $type .= " <i>" . \adminer\lang('Auto Increment') . "</i>";
            }
            if(\array_key_exists("default", $field))
            {
                $type .= /*' ' . \adminer\lang('Default value') .*/ ' [<b>' . \adminer\h($field["default"]) . '</b>]';
            }
            $detail = [
                'name' => \adminer\h($field["field"] ?? ''),
                'type' => $type,
                'collation' => \adminer\h($field["collation"] ?? ''),
            ];
            if($hasComment)
            {
                $detail['comment'] = \adminer\h($field["comment"] ?? '');
            }

            $details[] = $detail;
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get the triggers of a table
     *
     * @param string $table     The table name
     *
     * @return array|null
     */
    public function getViewTriggers(string $table)
    {
        $status = $this->status($table);
        if(!\adminer\support("view_trigger"))
        {
            return null;
        }

        // From table.inc.php
        $triggers = \adminer\triggers($table);
        $main_actions = [
            \adminer\lang('Add trigger'),
        ];

        $headers = [
            \adminer\lang('Name'),
            '&nbsp;',
            '&nbsp;',
            '&nbsp;',
        ];

        if(!$triggers)
        {
            $triggers = [];
        }
        $details = [];
        // From table.inc.php
        foreach($triggers as $key => $val)
        {
            $details[] = [
                \adminer\h($val[0]),
                \adminer\h($val[1]),
                \adminer\h($key),
                \adminer\lang('Alter'),
            ];
        }

        return \compact('main_actions', 'headers', 'details');
    }
}
