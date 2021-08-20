<?php

namespace Lagdo\Adminer\Db\Proxy;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class TableProxy extends AbstractProxy
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
            $this->tableStatus = $this->server->table_status1($table, true);
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
        $links = [
            'select' => $this->adminer->lang('Select data'),
        ];
        if($this->server->support('table') || $this->server->support('indexes'))
        {
            $links['table'] = $this->adminer->lang('Show structure');
        }
        if($this->server->support('table'))
        {
            $links['alter'] = $this->adminer->lang('Alter table');
        }
        if($set !== null)
        {
            $links['edit'] = $this->adminer->lang('New item');
        }
        // $links['docs'] = \doc_link([$this->server->jush => $this->driver->tableHelp($name)], '?');

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
        $main_actions = [
            'edit-table' => $this->adminer->lang('Alter table'),
            'drop-table' => $this->adminer->lang('Drop table'),
            'select-table' => $this->adminer->lang('Select'),
            'insert-table' => $this->adminer->lang('New item'),
        ];

        // From table.inc.php
        $status = $this->status($table);
        $name = $this->adminer->tableName($status);
        $title = $this->adminer->lang('Table') . ': ' . ($name != '' ? $name : $this->adminer->h($table));

        $comment = $status['Comment'] ?? '';

        $tabs = [
            'fields' => $this->adminer->lang('Columns'),
            // 'indexes' => $this->adminer->lang('Indexes'),
            // 'foreign-keys' => $this->adminer->lang('Foreign keys'),
            // 'triggers' => $this->adminer->lang('Triggers'),
        ];
        if($this->server->is_view($status))
        {
            if($this->server->support('view_trigger'))
            {
                $tabs['triggers'] = $this->adminer->lang('Triggers');
            }
        }
        else
        {
            if($this->server->support('indexes'))
            {
                $tabs['indexes'] = $this->adminer->lang('Indexes');
            }
            if($this->server->fk_support($status))
            {
                $tabs['foreign-keys'] = $this->adminer->lang('Foreign keys');
            }
            if($this->server->support('trigger'))
            {
                $tabs['triggers'] = $this->adminer->lang('Triggers');
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
        $fields = $this->server->fields($table);
        if(!$fields)
        {
            throw new Exception($this->server->error());
        }

        $main_actions = $this->getTableLinks();

        $tabs = [
            'fields' => $this->adminer->lang('Columns'),
            // 'indexes' => $this->adminer->lang('Indexes'),
            // 'foreign-keys' => $this->adminer->lang('Foreign keys'),
            // 'triggers' => $this->adminer->lang('Triggers'),
        ];
        if($this->server->support('indexes'))
        {
            $tabs['indexes'] = $this->adminer->lang('Indexes');
        }
        if($this->server->fk_support($this->status($table)))
        {
            $tabs['foreign-keys'] = $this->adminer->lang('Foreign keys');
        }
        if($this->server->support('trigger'))
        {
            $tabs['triggers'] = $this->adminer->lang('Triggers');
        }

        $headers = [
            $this->adminer->lang('Name'),
            $this->adminer->lang('Type'),
            $this->adminer->lang('Collation'),
        ];
        $hasComment = $this->server->support('comment');
        if($hasComment)
        {
            $headers[] = $this->adminer->lang('Comment');
        }

        $details = [];
        foreach($fields as $field)
        {
            $type = $this->adminer->h($field['full_type']);
            if($field['null'])
            {
                $type .= ' <i>nullable</i>'; // ' <i>NULL</i>';
            }
            if($field['auto_increment'])
            {
                $type .= ' <i>' . $this->adminer->lang('Auto Increment') . '</i>';
            }
            if(\array_key_exists('default', $field))
            {
                $type .= /*' ' . $this->adminer->lang('Default value') .*/ ' [<b>' . $this->adminer->h($field['default']) . '</b>]';
            }
            $detail = [
                'name' => $this->adminer->h($field['field'] ?? ''),
                'type' => $type,
                'collation' => $this->adminer->h($field['collation'] ?? ''),
            ];
            if($hasComment)
            {
                $detail['comment'] = $this->adminer->h($field['comment'] ?? '');
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
        if(!$this->server->support('indexes'))
        {
            return null;
        }

        // From table.inc.php
        $indexes = $this->server->indexes($table);
        $main_actions = [
            'create' => $this->adminer->lang('Alter indexes'),
        ];

        $headers = [
            $this->adminer->lang('Name'),
            $this->adminer->lang('Type'),
            $this->adminer->lang('Column'),
        ];

        $details = [];
        // From adminer.inc.php
        if(!$indexes)
        {
            $indexes = [];
        }
        foreach($indexes as $name => $index) {
            \ksort($index['columns']); // enforce correct columns order
            $print = [];
            foreach($index['columns'] as $key => $val)
            {
                $value = '<i>' . $this->adminer->h($val) . '</i>';
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
                'name' => $this->adminer->h($name),
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
        if(!$this->server->fk_support($status))
        {
            return null;
        }

        // From table.inc.php
        $foreign_keys = $this->server->foreign_keys($table);
        $main_actions = [
            $this->adminer->lang('Add foreign key'),
        ];

        $headers = [
            $this->adminer->lang('Name'),
            $this->adminer->lang('Source'),
            $this->adminer->lang('Target'),
            $this->adminer->lang('ON DELETE'),
            $this->adminer->lang('ON UPDATE'),
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
                $target .= '<b>' . $this->adminer->h($foreign_key['db']) . '</b>.';
            }
            if(\array_key_exists('ns', $foreign_key) && $foreign_key['ns'] != '')
            {
                $target .= '<b>' . $this->adminer->h($foreign_key['ns']) . '</b>.';
            }
            $target = $this->adminer->h($foreign_key['table']) .
                '(' . \implode(', ', \array_map(function($key) {
                    return $this->adminer->h($key);
                }, $foreign_key['target'])) . ')';
            $details[] = [
                'name' => $this->adminer->h($name),
                'source' => '<i>' . \implode('</i>, <i>',
                    \array_map(function($key) {
                        return $this->adminer->h($key);
                    }, $foreign_key['source'])) . '</i>',
                'target' => $target,
                'on_delete' => $this->adminer->h($foreign_key['on_delete']),
                'on_update' => $this->adminer->h($foreign_key['on_update']),
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
        if(!$this->server->support('trigger'))
        {
            return null;
        }

        // From table.inc.php
        $triggers = $this->server->triggers($table);
        $main_actions = [
            $this->adminer->lang('Add trigger'),
        ];

        $headers = [
            $this->adminer->lang('Name'),
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
                $this->adminer->h($val[0]),
                $this->adminer->h($val[1]),
                $this->adminer->h($key),
                $this->adminer->lang('Alter'),
            ];
        }

        return \compact('main_actions', 'headers', 'details');
    }

    /**
     * Get foreign keys
     *
     * @param string $table     The table name
     *
     * @return void
     */
    private function getForeignKeys(string $table = '')
    {
        $this->referencable_primary = $this->adminer->referencable_primary($table);
        $this->foreign_keys = [];
        foreach($this->referencable_primary as $table_name => $field)
        {
            $name = \str_replace('`', '``', $table_name) .
                '`' . \str_replace('`', '``', $field['field']);
            // not idf_escape() - used in JS
            $this->foreign_keys[$name] = $table_name;
        }
    }

    /**
     * Get field types
     *
     * @param string $type  The type name
     *
     * @return array
     */
    public function getFieldTypes(string $type = '')
    {
        // From includes/editing.inc.php
        $extra_types = [];
        if($type && !isset($this->server->types[$type]) &&
            !isset($this->foreign_keys[$type]) && !\in_array($type, $extra_types))
        {
            $extra_types[] = $type;
        }
        if($this->foreign_keys)
        {
            $this->server->structured_types[$this->adminer->lang('Foreign keys')] = $this->foreign_keys;
        }
        return \array_merge($extra_types, $this->server->structured_types);
    }

    /**
     * Get required data for create/update on tables
     *
     * @param string $table The table name
     *
     * @return array
     */
    public function getTableData(string $table = '')
    {
        $main_actions = [
            'table-save' => $this->adminer->lang('Save'),
            'table-cancel' => $this->adminer->lang('Cancel'),
        ];

        // From create.inc.php
        $status = [];
        $fields = [];
        if($table !== '')
        {
            $status = $this->server->table_status($table);
            if(!$status)
            {
                throw new Exception($this->adminer->lang('No tables.'));
            }
            $orig_fields = $this->server->fields($table);
            $fields = [];
            foreach($orig_fields as $field)
            {
                $field['has_default'] = isset($field['default']);
                $fields[] = $field;
            }
        }

        $this->getForeignKeys();

        $hasAutoIncrement = false;
        foreach($fields as &$field)
        {
            $hasAutoIncrement = $hasAutoIncrement && $field['auto_increment'];
            $field['has_default'] = isset($field['default']);
            $type = $field['type'];
            $field['_types_'] = $this->getFieldTypes($type);
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
            $field['_unsigned_hidden_'] = !(!$type || \preg_match($this->adminer->number_type(), $type));
            $field['_on_update_hidden_'] = !\preg_match('~timestamp|datetime~', $type);
            $field['_on_delete_hidden_'] = !\preg_match('~`~', $type);
        }
        $options = [
            'has_auto_increment' => $hasAutoIncrement,
            'on_update' => ['CURRENT_TIMESTAMP'],
            'on_delete' => \explode('|', $this->server->on_actions),
        ];

        // $_GET['create'] = true
        $this->adminer->input->isCreate = true;

        $collations = $this->server->collations();
        $engines = $this->server->engines();
        $support = [
            'columns' => $this->server->support('columns'),
            'comment' => $this->server->support('comment'),
            'partitioning' => $this->server->support('partitioning'),
            'move_col' => $this->server->support('move_col'),
            'drop_col' => $this->server->support('drop_col'),
        ];

        $foreign_keys = $this->foreign_keys;
        $unsigned = $this->server->unsigned;
        // Give the var a better name
        $table = $status;
        return \compact('main_actions', 'table', 'foreign_keys', 'fields',
            'options', 'collations', 'engines', 'support', 'unsigned');
    }

    /**
     * Get fields for a new column
     *
     * @return array
     */
    public function getTableField()
    {
        $this->getForeignKeys();

        return [
            'field' => '',
            'type' => '',
            'length' => '',
            'unsigned' => '',
            'null' => false,
            'auto_increment' => false,
            'collation' => '',
            'has_default' => false,
            'default' => null,
            'comment' => '',
            // 'primary' => true,
            // 'generated' => 0,
            'on_update' => '',
            'on_delete' => '',
            '_types_' => $this->getFieldTypes(),
            '_length_required_' => false,
            '_collation_hidden_' => true,
            '_unsigned_hidden_' => false,
            '_on_update_hidden_' => true,
            '_on_delete_hidden_' => true
        ];
    }

    /**
     * Create or alter a table
     *
     * @param array  $values    The table values
     * @param string $table     The table name
     *
     * @return array
     */
    private function createOrAlterTable(array $values, string $table,
        array $orig_fields, array $table_status, string $engine, string $collation, $comment)
    {
        // From create.inc.php
        $values['fields'] = (array)$values['fields'];
        if($values['auto_increment_col'])
        {
            $values['fields'][$values['auto_increment_col']]['auto_increment'] = true;
        }

        $fields = [];
        $all_fields = [];
        $use_all_fields = false;
        $foreign = [];
        $orig_field = \reset($orig_fields);
        $after = ' FIRST';

        $this->getForeignKeys();

        foreach($values['fields'] as $key => $field)
        {
            $foreign_key = $this->foreign_keys[$field['type']] ?? null;
            //! can collide with user defined type
            $type_field = ($foreign_key !== null ? $this->referencable_primary[$foreign_key] : $field);
            // Originally, deleted fields have the "field" field set to an empty string.
            // But in our implementation, the "field" field is deleted.
            // if($field['field'] != '')
            if(isset($field['field']) && $field['field'] != '')
            {
                if(!isset($field['has_default']))
                {
                    $field['default'] = null;
                }
                $field['auto_increment'] = ($key == $values['auto_increment_col']);
                $field["null"] = isset($field["null"]);

                $process_field = $this->adminer->process_field($field, $type_field);
                $all_fields[] = [$field['orig'], $process_field, $after];
                if(!$orig_field || $process_field != $this->adminer->process_field($orig_field, $orig_field))
                {
                    $fields[] = [$field['orig'], $process_field, $after];
                    if($field['orig'] != '' || $after)
                    {
                        $use_all_fields = true;
                    }
                }
                if($foreign_key !== null)
                {
                    $foreign[$this->server->idf_escape($field['field'])] = ($table != '' && $this->server->jush != 'sqlite' ? 'ADD' : ' ') .
                        $this->server->format_foreign_key([
                            'table' => $this->foreign_keys[$field['type']],
                            'source' => [$field['field']],
                            'target' => [$type_field['field']],
                            'on_delete' => $field['on_delete'],
                        ]);
                }
                $after = ' AFTER ' . $this->server->idf_escape($field['field']);
            }
            elseif($field['orig'] != '')
            {
                // A missing "field" field and a not empty "orig" field means the column is to be dropped.
                // We also append null in the array because the drivers code accesses field at position 1.
                $use_all_fields = true;
                $fields[] = [$field['orig'], null];
            }
            if($field['orig'] != '')
            {
                $orig_field = \next($orig_fields);
                if(!$orig_field)
                {
                    $after = '';
                }
            }
        }

        // For now, partitioning is not implemented
        $partitioning = '';
        // if($partition_by[$values['partition_by']])
        // {
        //     $partitions = [];
        //     if($values['partition_by'] == 'RANGE' || $values['partition_by'] == 'LIST')
        //     {
        //         foreach(\array_filter($values['partition_names']) as $key => $val)
        //         {
        //             $value = $values['partition_values'][$key];
        //             $partitions[] = "\n  PARTITION " . $this->server->idf_escape($val) .
        //                 ' VALUES ' . ($values['partition_by'] == 'RANGE' ? 'LESS THAN' : 'IN') .
        //                 ($value != '' ? ' ($value)' : ' MAXVALUE'); //! SQL injection
        //         }
        //     }
        //     $partitioning .= "\nPARTITION BY $values[partition_by]($values[partition])" .
        //         ($partitions // $values['partition'] can be expression, not only column
        //         ? ' (' . \implode(',', $partitions) . "\n)"
        //         : ($values['partitions'] ? ' PARTITIONS ' . (+$values['partitions']) : '')
        //     );
        // }
        // elseif($this->server->support('partitioning') &&
        //     \preg_match('~partitioned~', $table_status['Create_options']))
        // {
        //     $partitioning .= "\nREMOVE PARTITIONING";
        // }

        $name = \trim($values['name']);
        $autoIncrement = $this->adminer->input()->getAutoIncrementStep();
        if($this->server->jush == 'sqlite' && ($use_all_fields || $foreign)) {
            $fields = $all_fields;
        }

        $success = $this->server->alter_table($table, $name, $fields, $foreign,
            $comment, $engine, $collation, $autoIncrement, $partitioning);

        $message = $table == '' ?
            $this->adminer->lang('Table has been created.') :
            $this->adminer->lang('Table has been altered.');

        $error = $this->server->error();

        // From functions.inc.php
        // queries_redirect(ME . (support('table') ? 'table=' : 'select=') . urlencode($name), $message, $redirect);

        return \compact('success', 'message', 'error');
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
        $orig_fields = [];
        $table_status = [];

        $comment = $values['comment'] ?? null;
        $engine = $values['engine'] ?? '';
        $collation = $values['collation'] ?? '';

        return $this->createOrAlterTable($values, '',
            $orig_fields, $table_status, $engine, $collation, $comment);
    }

    /**
     * Alter a table
     *
     * @param string $table     The table name
     * @param array  $values    The table values
     *
     * @return array
     */
    public function alterTable(string $table, array $values)
    {
        $orig_fields = $this->server->fields($table);
        $table_status = $this->server->table_status($table);
        if(!$table_status)
        {
            throw new Exception($this->adminer->lang('No tables.'));
        }

        $currComment = $table_status['Comment'] ?? null;
        $currEngine = $table_status['Engine'] ?? '';
        $currCollation = $table_status['Collation'] ?? '';
        $comment = $values['comment'] != $currComment ? $values['comment'] : null;
        $engine = $values['engine'] != $currEngine ? $values['engine'] : '';
        $collation = $values['collation'] != $currCollation ? $values['collation'] : '';

        return $this->createOrAlterTable($values, $table,
            $orig_fields, $table_status, $engine, $collation, $comment);
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

        $success = $this->server->drop_tables([$table]);

        $error = $this->server->error();

        $message = $this->adminer->lang('Table has been dropped.');

        return \compact('success', 'message', 'error');
    }
}
