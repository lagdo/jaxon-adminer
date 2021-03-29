<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class TableProxy
{
    /**
     * The current table status
     *
     * @var mixed
     */
    protected $tableStatus = null;

    /**
     * Get the current table status
     *
     * @param string $table
     *
     * @return mixed
     */
    protected function status(string $table)
    {
        if(!$this->tableStatus)
        {
            $this->tableStatus = \adminer\table_status1($table, true);
        }
        return $this->tableStatus;
    }

    /**
     * Print links after select heading
     * Copied from selectLinks() in adminer.inc.php
     *
     * @param array result of SHOW TABLE STATUS
     * @param string new item options, NULL for no new item
     *
     * @return array
     */
    protected function getTableLinks($tableStatus, $set = "")
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
            if(\adminer\is_view($tableStatus))
            {
                $links["view"] = \adminer\lang('Alter view');
            }
            else
            {
                $links["create"] = \adminer\lang('Alter table');
            }
        }
        if($set !== null)
        {
            $links["edit"] = \adminer\lang('New item');
        }
        // $name = $tableStatus["Name"];
        // $links['docs'] = \doc_link([$jush => $driver->tableHelp($name)], "?");

        return $links;
    }

    /**
     * Get details about a table or a view
     *
     * @param string $table     The table name
     *
     * @return array
     */
    public function getTableInfo(string $table)
    {
        global $adminer;

        // From table.inc.php
        $table_status = $this->status($table);
        $name = $adminer->tableName($table_status);
        $title = (\adminer\is_view($table_status) ?
            ($table_status['Engine'] == 'materialized view' ?
            \adminer\lang('Materialized view') : \adminer\lang('View')) :
            \adminer\lang('Table')) . ": " . ($name != "" ? $name : \adminer\h($table));

        $comment = $table_status["Comment"] ?? '';

        $tabs = [
            'fields' => \adminer\lang('Columns'),
            // 'indexes' => \adminer\lang('Indexes'),
            // 'foreign-keys' => \adminer\lang('Foreign keys'),
            // 'triggers' => \adminer\lang('Triggers'),
        ];
        if(\adminer\is_view($table_status))
        {
            if(\adminer\support("view_trigger"))
            {
                $tabs['triggers'] = \adminer\lang('Triggers');
            }
        }
        else
        {
            if(\adminer\support("indexes"))
            {
                $tabs['indexes'] = \adminer\lang('Indexes');
            }
            if(\adminer\fk_support($table_status))
            {
                $tabs['foreign-keys'] = \adminer\lang('Foreign keys');
            }
            if(\adminer\support("trigger"))
            {
                $tabs['triggers'] = \adminer\lang('Triggers');
            }
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
    public function getTableFields(string $table)
    {
        // From table.inc.php
        $fields = \adminer\fields($table);
        if(!$fields)
        {
            throw new Exception(\error());
        }

        $table_status = $this->status($table);
        $main_actions = $this->getTableLinks($table_status);

        $tabs = [
            'fields' => \adminer\lang('Columns'),
            // 'indexes' => \adminer\lang('Indexes'),
            // 'foreign-keys' => \adminer\lang('Foreign keys'),
            // 'triggers' => \adminer\lang('Triggers'),
        ];
        if(\adminer\is_view($table_status))
        {
            if(\adminer\support("view_trigger"))
            {
                $tabs['triggers'] = \adminer\lang('Triggers');
            }
        }
        else
        {
            if(\adminer\support("indexes"))
            {
                $tabs['indexes'] = \adminer\lang('Indexes');
            }
            if(\adminer\fk_support($table_status))
            {
                $tabs['foreign-keys'] = \adminer\lang('Foreign keys');
            }
            if(\adminer\support("trigger"))
            {
                $tabs['triggers'] = \adminer\lang('Triggers');
            }
        }

        $hasComment = \adminer\support('comment');

        $headers = [
            \adminer\lang('Name'),
            \adminer\lang('Type'),
            \adminer\lang('Collation'),
        ];
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
     * Get the indexes of a table
     *
     * @param string $table     The table name
     *
     * @return array|null
     */
    public function getTableIndexes(string $table)
    {
        $table_status = $this->status($table);
        if(\adminer\is_view($table_status) || !\adminer\support("indexes"))
        {
            return null;
        }

        // From table.inc.php
        $indexes = \adminer\indexes($table);
        $main_actions = [
            'create' => \adminer\lang('Alter indexes'),
        ];

        $headers = [
            \adminer\lang('Name'),
            \adminer\lang('Type'),
            \adminer\lang('Column'),
        ];

        $details = [];
        // From adminer.inc.php
        if(!$indexes)
        {
            $indexes = [];
        }
        foreach ($indexes as $name => $index) {
            \ksort($index["columns"]); // enforce correct columns order
            $print = [];
            foreach($index["columns"] as $key => $val)
            {
                $value = "<i>" . \adminer\h($val) . "</i>";
                if(\array_key_exists("lengths", $index) &&
                    \is_array($index["lengths"]) &&
                    \array_key_exists($key, $index["lengths"]))
                {
                    $value .= "(" . $index["lengths"][$key] . ")";
                }
                if(\array_key_exists("descs", $index) &&
                    \is_array($index["descs"]) &&
                    \array_key_exists($key, $index["descs"]))
                {
                    $value .= " DESC";
                }
                $print[] = $value;
            }
            $details[] = [
                'name' => \adminer\h($name),
                'type' => $index['type'],
                'desc' => \implode(", ", $print),
            ];
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get the foreign keys of a table
     *
     * @param string $table     The table name
     *
     * @return array|null
     */
    public function getTableForeignKeys(string $table)
    {
        $table_status = $this->status($table);
        if(\adminer\is_view($table_status) || !\adminer\fk_support($table_status))
        {
            return null;
        }

        // From table.inc.php
        $foreign_keys = \adminer\foreign_keys($table);
        $main_actions = [
            \adminer\lang('Add foreign key'),
        ];

        $headers = [
            \adminer\lang('Name'),
            \adminer\lang('Source'),
            \adminer\lang('Target'),
            \adminer\lang('ON DELETE'),
            \adminer\lang('ON UPDATE'),
        ];

        if(!$foreign_keys)
        {
            $foreign_keys = [];
        }
        $details = [];
        // From table.inc.php
        foreach($foreign_keys as $name => $foreign_key)
        {
            $target = '';
            if(\array_key_exists("db", $foreign_key) && $foreign_key["db"] != "")
            {
                $target .= "<b>" . \adminer\h($foreign_key["db"]) . "</b>.";
            }
            if(\array_key_exists("ns", $foreign_key) && $foreign_key["ns"] != "")
            {
                $target .= "<b>" . \adminer\h($foreign_key["ns"]) . "</b>.";
            }
            $target = \adminer\h($foreign_key["table"]) .
                '(' . \implode(', ', \array_map('\\adminer\\h', $foreign_key["target"])) . ')';
            $details[] = [
                'name' => \adminer\h($name),
                'source' => "<i>" . \implode("</i>, <i>",
                    \array_map('\\adminer\\h', $foreign_key["source"])) . "</i>",
                'target' => $target,
                'on_delete' => \adminer\h($foreign_key["on_delete"]),
                'on_update' => \adminer\h($foreign_key["on_update"]),
            ];
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
    public function getTableTriggers(string $table)
    {
        $table_status = $this->status($table);
        if(!\adminer\support(\adminer\is_view($table_status) ? "view_trigger" : "trigger"))
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
