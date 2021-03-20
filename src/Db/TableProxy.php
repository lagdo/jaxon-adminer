<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class TableProxy
{
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
            "select" => \lang('Select data'),
        ];
        if(\support("table") || \support("indexes"))
        {
            $links["table"] = \lang('Show structure');
        }
        if(\support("table"))
        {
            if(\is_view($tableStatus))
            {
                $links["view"] = \lang('Alter view');
            }
            else
            {
                $links["create"] = \lang('Alter table');
            }
        }
        if($set !== null)
        {
            $links["edit"] = \lang('New item');
        }
        // $name = $tableStatus["Name"];
        // $links['docs'] = \doc_link([$jush => $driver->tableHelp($name)], "?");

        return $links;
    }

    /**
     * Get details about a table or a view
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     * @param string $table     The table name
     *
     * @return array
     */
    public function getTableFields(array $options, string $database, string $table)
    {
        global $adminer;

        // From table.inc.php
        $fields = \fields($table);
        if(!$fields)
        {
            throw new Exception(\error());
        }
        $table_status = \table_status1($table, true);
        $name = $adminer->tableName($table_status);
        $title = ($fields && \is_view($table_status) ?
            ($table_status['Engine'] == 'materialized view' ?
            \lang('Materialized view') : \lang('View')) :
            \lang('Table')) . ": " . ($name != "" ? $name : \h($table));

        $comment = $table_status["Comment"];

        $main_actions = $this->getTableLinks($table_status);

        $hasComment = \support('comment');

        $tabs = [
            'fields' => \is_view($table_status) ? \lang('View') : \lang('Table'),
            'indexes' => \lang('Indexes'),
            'foreign-keys' => \lang('Foreign keys'),
            'triggers' => \lang('Triggers'),
        ];

        $headers = [
            \lang('Column'),
            \lang('Type'),
            \lang('Collation'),
        ];
        if($hasComment)
        {
            $headers[] = \lang('Comment');
        }

        $details = [];
        foreach($fields as $field)
        {
            $type = \h($field["full_type"]);
            if($field["null"])
            {
                $type .= " <i>nullable</i>"; // " <i>NULL</i>";
            }
            if($field["auto_increment"])
            {
                $type .= " <i>" . \lang('Auto Increment') . "</i>";
            }
            if(isset($field["default"]))
            {
                $type .= /*' ' . \lang('Default value') .*/ ' [<b>' . \h($field["default"]) . '</b>]';
            }
            $detail = [
                'name' => \h($field["field"]),
                'type' => $type,
                'collation' => \h($field["collation"]),
            ];
            if($hasComment)
            {
                $detail['comment'] = \h($field["comment"]);
            }

            $details[] = $detail;
        }

        return \compact('title', 'comment', 'tabs', 'main_actions', 'headers', 'details');
    }

    /**
     * Get the indexes of a table
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     * @param string $table     The table name
     *
     * @return array|null
     */
    public function getTableIndexes(array $options, string $database, string $table)
    {
        $table_status = \table_status1($table, true);
        if(\is_view($table_status) || !support("indexes"))
        {
            return null;
        }

        // From table.inc.php
        $indexes = \indexes($table);
        $main_actions = [
            'create' => \lang('Alter indexes'),
        ];

        $headers = [
            \lang('Name'),
            \lang('Type'),
            \lang('Column'),
        ];

        $details = [];
        // From adminer.inc.php
        if(!$indexes)
        {
            $indexes = [];
        }
        foreach ($indexes as $name => $index) {
            ksort($index["columns"]); // enforce correct columns order
            $print = [];
            foreach ($index["columns"] as $key => $val) {
                $print[] = "<i>" . h($val) . "</i>"
                    . ($index["lengths"][$key] ? "(" . $index["lengths"][$key] . ")" : "")
                    . ($index["descs"][$key] ? " DESC" : "")
                ;
            }
            $details[] = [
                'name' => \h($name),
                'type' => $index['type'],
                'desc' => \implode(", ", $print),
            ];
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get the foreign keys of a table
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     * @param string $table     The table name
     *
     * @return array|null
     */
    public function getTableForeignKeys(array $options, string $database, string $table)
    {
        $table_status = \table_status1($table, true);
        if(\is_view($table_status) || !fk_support($table_status))
        {
            return null;
        }

        // From table.inc.php
        $foreign_keys = \foreign_keys($table);
        $main_actions = [
            \lang('Add foreign key'),
        ];

        $headers = [
            \lang('Name'),
            \lang('Source'),
            \lang('Target'),
            \lang('ON DELETE'),
            \lang('ON UPDATE'),
        ];

        if(!$foreign_keys)
        {
            $foreign_keys = [];
        }
        $details = [];
        // From table.inc.php
        foreach($foreign_keys as $name => $foreign_key)
        {
            $target = (isset($foreign_key["db"]) && $foreign_key["db"] != "" ? "<b>" . \h($foreign_key["db"]) . "</b>." : "") .
                (isset($foreign_key["ns"]) && $foreign_key["ns"] != "" ? "<b>" . \h($foreign_key["ns"]) . "</b>." : "") .
                \h($foreign_key["table"]) . '(' . \implode(', ', \array_map('h', $foreign_key["target"])) . ')';
            $details[] = [
                'name' => \h($name),
                'source' => "<i>" . \implode("</i>, <i>", \array_map('h', $foreign_key["source"])) . "</i>",
                'target' => $target,
                'on_delete' => \h($foreign_key["on_delete"]),
                'on_update' => \h($foreign_key["on_update"]),
            ];
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get the triggers of a table
     *
     * @param array $options    The corresponding config options
     * @param string $database  The database name
     * @param string $table     The table name
     *
     * @return array|null
     */
    public function getTableTriggers(array $options, string $database, string $table)
    {
        $table_status = \table_status1($table, true);
        if(!\support(\is_view($table_status) ? "view_trigger" : "trigger"))
        {
            return null;
        }

        // From table.inc.php
        $triggers = \triggers($table);
        $main_actions = [
            \lang('Add trigger'),
        ];

        $headers = ['', '', '', ''];

        if(!$triggers)
        {
            $triggers = [];
        }
        $details = [];
        // From table.inc.php
        foreach($triggers as $key => $val)
        {
            $details[] = [
                \h($val[0]),
                \h($val[1]),
                \h($key),
                \lang('Alter'),
            ];
        }

        return \compact('main_actions', 'headers', 'details');
    }
}
