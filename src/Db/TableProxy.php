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
     * @param string new item options, NULL for no new item
     *
     * @return array
     */
    protected function getTableLinks($set = null)
    {
        global $jush, $driver;

        $links = [
            'select' => \adminer\lang('Select data'),
        ];
        if(\adminer\support('table') || \adminer\support('indexes'))
        {
            $links['table'] = \adminer\lang('Show structure');
        }
        if(\adminer\support('table'))
        {
            $links['alter'] = \adminer\lang('Alter table');
        }
        if($set !== null)
        {
            $links['edit'] = \adminer\lang('New item');
        }
        // $links['docs'] = \doc_link([$jush => $driver->tableHelp($name)], '?');

        return $links;
    }

    /**
     * Get details about a table
     *
     * @param string $table     The table name
     *
     * @return array
     */
    public function getTableInfo(string $table)
    {
        global $adminer;

        $main_actions = [
            'edit-table' => \adminer\lang('Alter table'),
            'drop-table' => \adminer\lang('Drop table'),
        ];

        // From table.inc.php
        $status = $this->status($table);
        $name = $adminer->tableName($status);
        $title = \adminer\lang('Table') . ': ' . ($name != '' ? $name : \adminer\h($table));

        $comment = $status['Comment'] ?? '';

        $tabs = [
            'fields' => \adminer\lang('Columns'),
            // 'indexes' => \adminer\lang('Indexes'),
            // 'foreign-keys' => \adminer\lang('Foreign keys'),
            // 'triggers' => \adminer\lang('Triggers'),
        ];
        if(\adminer\is_view($status))
        {
            if(\adminer\support('view_trigger'))
            {
                $tabs['triggers'] = \adminer\lang('Triggers');
            }
        }
        else
        {
            if(\adminer\support('indexes'))
            {
                $tabs['indexes'] = \adminer\lang('Indexes');
            }
            if(\adminer\fk_support($status))
            {
                $tabs['foreign-keys'] = \adminer\lang('Foreign keys');
            }
            if(\adminer\support('trigger'))
            {
                $tabs['triggers'] = \adminer\lang('Triggers');
            }
        }

        return \compact('main_actions', 'title', 'comment', 'tabs');
    }

    /**
     * Get the fields of a table
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
            throw new Exception(\adminer\error());
        }

        $main_actions = $this->getTableLinks();

        $tabs = [
            'fields' => \adminer\lang('Columns'),
            // 'indexes' => \adminer\lang('Indexes'),
            // 'foreign-keys' => \adminer\lang('Foreign keys'),
            // 'triggers' => \adminer\lang('Triggers'),
        ];
        if(\adminer\support('indexes'))
        {
            $tabs['indexes'] = \adminer\lang('Indexes');
        }
        if(\adminer\fk_support($this->status($table)))
        {
            $tabs['foreign-keys'] = \adminer\lang('Foreign keys');
        }
        if(\adminer\support('trigger'))
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
            $type = \adminer\h($field['full_type']);
            if($field['null'])
            {
                $type .= ' <i>nullable</i>'; // ' <i>NULL</i>';
            }
            if($field['auto_increment'])
            {
                $type .= ' <i>' . \adminer\lang('Auto Increment') . '</i>';
            }
            if(\array_key_exists('default', $field))
            {
                $type .= /*' ' . \adminer\lang('Default value') .*/ ' [<b>' . \adminer\h($field['default']) . '</b>]';
            }
            $detail = [
                'name' => \adminer\h($field['field'] ?? ''),
                'type' => $type,
                'collation' => \adminer\h($field['collation'] ?? ''),
            ];
            if($hasComment)
            {
                $detail['comment'] = \adminer\h($field['comment'] ?? '');
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
        if(!\adminer\support('indexes'))
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
            \ksort($index['columns']); // enforce correct columns order
            $print = [];
            foreach($index['columns'] as $key => $val)
            {
                $value = '<i>' . \adminer\h($val) . '</i>';
                if(\array_key_exists('lengths', $index) &&
                    \is_array($index['lengths']) &&
                    \array_key_exists($key, $index['lengths']))
                {
                    $value .= '(' . $index['lengths'][$key] . ')';
                }
                if(\array_key_exists('descs', $index) &&
                    \is_array($index['descs']) &&
                    \array_key_exists($key, $index['descs']))
                {
                    $value .= ' DESC';
                }
                $print[] = $value;
            }
            $details[] = [
                'name' => \adminer\h($name),
                'type' => $index['type'],
                'desc' => \implode(', ', $print),
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
        $status = $this->status($table);
        if(!\adminer\fk_support($status))
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
            if(\array_key_exists('db', $foreign_key) && $foreign_key['db'] != '')
            {
                $target .= '<b>' . \adminer\h($foreign_key['db']) . '</b>.';
            }
            if(\array_key_exists('ns', $foreign_key) && $foreign_key['ns'] != '')
            {
                $target .= '<b>' . \adminer\h($foreign_key['ns']) . '</b>.';
            }
            $target = \adminer\h($foreign_key['table']) .
                '(' . \implode(', ', \array_map('\\adminer\\h', $foreign_key['target'])) . ')';
            $details[] = [
                'name' => \adminer\h($name),
                'source' => '<i>' . \implode('</i>, <i>',
                    \array_map('\\adminer\\h', $foreign_key['source'])) . '</i>',
                'target' => $target,
                'on_delete' => \adminer\h($foreign_key['on_delete']),
                'on_update' => \adminer\h($foreign_key['on_update']),
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
        $status = $this->status($table);
        if(!\adminer\support('trigger'))
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

    /**
     * Get required data for create/update on tables
     *
     * @param string     The table name
     *
     * @return array
     */
    public function getTableData(string $table = '')
    {
        // From includes/editing.inc.php
        global $structured_types, $types, $unsigned, $on_actions;

        $main_actions = [
            'table-cancel' => \adminer\lang('Cancel'),
        ];

        // From create.inc.php
        $status = [];
        $fields = [];
        if($table !== '')
        {
            $status = \adminer\table_status($table);
            if(!$status)
            {
                throw new Exception(\adminer\lang('No tables.'));
            }
            $fields = \adminer\fields($table);
        }

        $referencable_primary = \adminer\referencable_primary($table);
        $foreign_keys = [];
        foreach($referencable_primary as $table_name => $field)
        {
            $name = \str_replace('`', '``', $table_name) . '`' . \str_replace('`', '``', $field['field']);
            // not idf_escape() - used in JS
            $foreign_keys[$name] = $table_name;
        }

        foreach($fields as &$field)
        {
            $field["has_default"] = isset($field["default"]);
            // From includes/editing.inc.php
            $extra_types = [];
            $type = $field['type'];
            if($type && !isset($types[$type]) &&
                !isset($foreign_keys[$type]) && !\in_array($type, $extra_types))
            {
                $extra_types[] = $type;
            }
            if($foreign_keys)
            {
                $structured_types[\adminer\lang('Foreign keys')] = $foreign_keys;
            }
            $field['_types_'] = \array_merge($extra_types, $structured_types);
            if(!isset($field['on_update']))
            {
                $field['on_update'] = '';
            }
            if(!isset($field['on_delete']))
            {
                $field['on_delete'] = '';
            }
            if(\preg_match('~^CURRENT_TIMESTAMP~i', $field['on_update']))
            {
                $field['on_update'] = 'CURRENT_TIMESTAMP';
            }

            $field['_length_required_'] = !$field['length'] && \preg_match('~var(char|binary)$~', $type);
            $field['_collation_hidden_'] = !\preg_match('~(char|text|enum|set)$~', $type);
            $field['_unsigned_hidden_'] = !(!$type || \preg_match(\adminer\number_type(), $type));
            $field['_on_update_hidden_'] = !\preg_match('~timestamp|datetime~', $type);
            $field['_on_delete_hidden_'] = !\preg_match('~`~', $type);
        }
        $options = [
            'on_update' => ['CURRENT_TIMESTAMP'],
            'on_delete' => \explode('|', $on_actions),
        ];


        $collations = \adminer\collations();
        $engines = \adminer\engines();
        $support = [
            'columns' => \adminer\support('columns'),
            'comment' => \adminer\support('comment'),
            'partitioning' => \adminer\support('partitioning'),
        ];

        // Give the var a better name
        $table = $status;
        return \compact('main_actions', 'table', 'foreign_keys', 'fields',
            'options', 'collations', 'engines', 'support', 'unsigned');
    }

    /**
     * Get a table
     *
     * @param string $table     The table name
     *
     * @return array
     */
    public function getTable(string $table)
    {
        global $jush, $error;

    }

    /**
     * Create a table
     *
     * @param array  $values    The table values
     *
     * @return array
     */
    public function createTable(array $values)
    {
        global $jush, $error;

    }

    /**
     * Update a table
     *
     * @param string $table     The table name
     * @param array  $values    The table values
     *
     * @return array
     */
    public function updateTable(string $table, array $values)
    {
        global $jush, $error;

    }

    /**
     * Drop a table
     *
     * @param string $table     The table name
     *
     * @return array
     */
    public function dropTable(string $table)
    {
        global $jush, $error;

    }
}
