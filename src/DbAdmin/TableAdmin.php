<?php

namespace Lagdo\Adminer\DbAdmin;

use Exception;

/**
 * Admin table functions
 */
class TableAdmin extends AbstractAdmin
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
        if (!$this->tableStatus) {
            $this->tableStatus = $this->db->tableStatusOrName($table, true);
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
            'select' => $this->util->lang('Select data'),
        ];
        if ($this->db->support('table') || $this->db->support('indexes')) {
            $links['table'] = $this->util->lang('Show structure');
        }
        if ($this->db->support('table')) {
            $links['alter'] = $this->util->lang('Alter table');
        }
        if ($set !== null) {
            $links['edit'] = $this->util->lang('New item');
        }
        // $links['docs'] = \doc_link([$this->db->jush() => $this->db->tableHelp($name)], '?');

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
        $mainActions = [
            'edit-table' => $this->util->lang('Alter table'),
            'drop-table' => $this->util->lang('Drop table'),
            'select-table' => $this->util->lang('Select'),
            'insert-table' => $this->util->lang('New item'),
        ];

        // From table.inc.php
        $status = $this->status($table);
        $name = $this->util->tableName($status);
        $title = $this->util->lang('Table') . ': ' . ($name != '' ? $name : $this->util->html($table));

        $comment = $status['Comment'] ?? '';

        $tabs = [
            'fields' => $this->util->lang('Columns'),
            // 'indexes' => $this->util->lang('Indexes'),
            // 'foreign-keys' => $this->util->lang('Foreign keys'),
            // 'triggers' => $this->util->lang('Triggers'),
        ];
        if ($this->db->isView($status)) {
            if ($this->db->support('view_trigger')) {
                $tabs['triggers'] = $this->util->lang('Triggers');
            }
        } else {
            if ($this->db->support('indexes')) {
                $tabs['indexes'] = $this->util->lang('Indexes');
            }
            if ($this->db->supportForeignKeys($status)) {
                $tabs['foreign-keys'] = $this->util->lang('Foreign keys');
            }
            if ($this->db->support('trigger')) {
                $tabs['triggers'] = $this->util->lang('Triggers');
            }
        }

        return \compact('mainActions', 'title', 'comment', 'tabs');
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
        $fields = $this->db->fields($table);
        if (!$fields) {
            throw new Exception($this->util->error());
        }

        $mainActions = $this->getTableLinks();

        $tabs = [
            'fields' => $this->util->lang('Columns'),
            // 'indexes' => $this->util->lang('Indexes'),
            // 'foreign-keys' => $this->util->lang('Foreign keys'),
            // 'triggers' => $this->util->lang('Triggers'),
        ];
        if ($this->db->support('indexes')) {
            $tabs['indexes'] = $this->util->lang('Indexes');
        }
        if ($this->db->supportForeignKeys($this->status($table))) {
            $tabs['foreign-keys'] = $this->util->lang('Foreign keys');
        }
        if ($this->db->support('trigger')) {
            $tabs['triggers'] = $this->util->lang('Triggers');
        }

        $headers = [
            $this->util->lang('Name'),
            $this->util->lang('Type'),
            $this->util->lang('Collation'),
        ];
        $hasComment = $this->db->support('comment');
        if ($hasComment) {
            $headers[] = $this->util->lang('Comment');
        }

        $details = [];
        foreach ($fields as $field) {
            $type = $this->util->html($field['full_type']);
            if ($field['null']) {
                $type .= ' <i>nullable</i>'; // ' <i>NULL</i>';
            }
            if ($field['auto_increment']) {
                $type .= ' <i>' . $this->util->lang('Auto Increment') . '</i>';
            }
            if (\array_key_exists('default', $field)) {
                $type .= /*' ' . $this->util->lang('Default value') .*/ ' [<b>' . $this->util->html($field['default']) . '</b>]';
            }
            $detail = [
                'name' => $this->util->html($field['field'] ?? ''),
                'type' => $type,
                'collation' => $this->util->html($field['collation'] ?? ''),
            ];
            if ($hasComment) {
                $detail['comment'] = $this->util->html($field['comment'] ?? '');
            }

            $details[] = $detail;
        }

        return \compact('mainActions', 'headers', 'details');
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
        if (!$this->db->support('indexes')) {
            return null;
        }

        // From table.inc.php
        $indexes = $this->db->indexes($table);
        $mainActions = [
            'create' => $this->util->lang('Alter indexes'),
        ];

        $headers = [
            $this->util->lang('Name'),
            $this->util->lang('Type'),
            $this->util->lang('Column'),
        ];

        $details = [];
        // From adminer.inc.php
        if (!$indexes) {
            $indexes = [];
        }
        foreach ($indexes as $name => $index) {
            \ksort($index['columns']); // enforce correct columns order
            $print = [];
            foreach ($index['columns'] as $key => $val) {
                $value = '<i>' . $this->util->html($val) . '</i>';
                if (\array_key_exists('lengths', $index) &&
                    \is_array($index['lengths']) &&
                    \array_key_exists($key, $index['lengths'])) {
                    $value .= '(' . $index['lengths'][$key] . ')';
                }
                if (\array_key_exists('descs', $index) &&
                    \is_array($index['descs']) &&
                    \array_key_exists($key, $index['descs'])) {
                    $value .= ' DESC';
                }
                $print[] = $value;
            }
            $details[] = [
                'name' => $this->util->html($name),
                'type' => $index['type'],
                'desc' => \implode(', ', $print),
            ];
        }

        return \compact('mainActions', 'headers', 'details');
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
        if (!$this->db->supportForeignKeys($status)) {
            return null;
        }

        // From table.inc.php
        $foreignKeys = $this->db->foreignKeys($table);
        $mainActions = [
            $this->util->lang('Add foreign key'),
        ];

        $headers = [
            $this->util->lang('Name'),
            $this->util->lang('Source'),
            $this->util->lang('Target'),
            $this->util->lang('ON DELETE'),
            $this->util->lang('ON UPDATE'),
        ];

        if (!$foreignKeys) {
            $foreignKeys = [];
        }
        $details = [];
        // From table.inc.php
        foreach ($foreignKeys as $name => $foreignKey) {
            $target = '';
            if (\array_key_exists('db', $foreignKey) && $foreignKey['db'] != '') {
                $target .= '<b>' . $this->util->html($foreignKey['db']) . '</b>.';
            }
            if (\array_key_exists('ns', $foreignKey) && $foreignKey['ns'] != '') {
                $target .= '<b>' . $this->util->html($foreignKey['ns']) . '</b>.';
            }
            $target = $this->util->html($foreignKey['table']) .
                '(' . \implode(', ', \array_map(function ($key) {
                    return $this->util->html($key);
                }, $foreignKey['target'])) . ')';
            $details[] = [
                'name' => $this->util->html($name),
                'source' => '<i>' . \implode(
                    '</i>, <i>',
                    \array_map(function ($key) {
                        return $this->util->html($key);
                    }, $foreignKey['source'])
                ) . '</i>',
                'target' => $target,
                'on_delete' => $this->util->html($foreignKey['on_delete']),
                'on_update' => $this->util->html($foreignKey['on_update']),
            ];
        }

        return \compact('mainActions', 'headers', 'details');
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
        if (!$this->db->support('trigger')) {
            return null;
        }

        // From table.inc.php
        $triggers = $this->db->triggers($table);
        $mainActions = [
            $this->util->lang('Add trigger'),
        ];

        $headers = [
            $this->util->lang('Name'),
            '&nbsp;',
            '&nbsp;',
            '&nbsp;',
        ];

        if (!$triggers) {
            $triggers = [];
        }
        $details = [];
        // From table.inc.php
        foreach ($triggers as $key => $val) {
            $details[] = [
                $this->util->html($val[0]),
                $this->util->html($val[1]),
                $this->util->html($key),
                $this->util->lang('Alter'),
            ];
        }

        return \compact('mainActions', 'headers', 'details');
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
        $this->referencableTables = $this->util->referencableTables($table);
        $this->foreignKeys = [];
        foreach ($this->referencableTables as $tableName => $field) {
            $name = \str_replace('`', '``', $tableName) .
                '`' . \str_replace('`', '``', $field['field']);
            // not escapeId() - used in JS
            $this->foreignKeys[$name] = $tableName;
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
        $extraTypes = [];
        if ($type && !$this->db->typeExists($type) &&
            !isset($this->foreignKeys[$type]) && !\in_array($type, $extraTypes)) {
            $extraTypes[] = $type;
        }
        if ($this->foreignKeys) {
            $this->db->setStructuredType($this->util->lang('Foreign keys'), $this->foreignKeys);
        }
        return \array_merge($extraTypes, $this->db->structuredTypes());
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
        $mainActions = [
            'table-save' => $this->util->lang('Save'),
            'table-cancel' => $this->util->lang('Cancel'),
        ];

        // From create.inc.php
        $status = [];
        $fields = [];
        if ($table !== '') {
            $status = $this->db->tableStatus($table);
            if (!$status) {
                throw new Exception($this->util->lang('No tables.'));
            }
            $orig_fields = $this->db->fields($table);
            $fields = [];
            foreach ($orig_fields as $field) {
                $field['has_default'] = isset($field['default']);
                $fields[] = $field;
            }
        }

        $this->getForeignKeys();

        $hasAutoIncrement = false;
        foreach ($fields as &$field) {
            $hasAutoIncrement = $hasAutoIncrement && $field['auto_increment'];
            $field['has_default'] = isset($field['default']);
            $type = $field['type'];
            $field['_types_'] = $this->getFieldTypes($type);
            if (!isset($field['on_update'])) {
                $field['on_update'] = '';
            }
            if (!isset($field['on_delete'])) {
                $field['on_delete'] = '';
            }
            if (\preg_match('~^CURRENT_TIMESTAMP~i', $field['on_update'])) {
                $field['on_update'] = 'CURRENT_TIMESTAMP';
            }

            $field['_length_required_'] = !$field['length'] && \preg_match('~var(char|binary)$~', $type);
            $field['_collation_hidden_'] = !\preg_match('~(char|text|enum|set)$~', $type);
            $field['_unsigned_hidden_'] = !(!$type || \preg_match($this->db->numberRegex(), $type));
            $field['_on_update_hidden_'] = !\preg_match('~timestamp|datetime~', $type);
            $field['_on_delete_hidden_'] = !\preg_match('~`~', $type);
        }
        $options = [
            'has_auto_increment' => $hasAutoIncrement,
            'on_update' => ['CURRENT_TIMESTAMP'],
            'on_delete' => $this->db->onActions(),
        ];

        $collations = $this->db->collations();
        $engines = $this->db->engines();
        $support = [
            'columns' => $this->db->support('columns'),
            'comment' => $this->db->support('comment'),
            'partitioning' => $this->db->support('partitioning'),
            'move_col' => $this->db->support('move_col'),
            'drop_col' => $this->db->support('drop_col'),
        ];

        $foreignKeys = $this->foreignKeys;
        $unsigned = $this->db->unsigned();
        // Give the var a better name
        $table = $status;
        return \compact(
            'mainActions',
            'table',
            'foreignKeys',
            'fields',
            'options',
            'collations',
            'engines',
            'support',
            'unsigned'
        );
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
    private function createOrAlterTable(
        array $values,
        string $table,
        array $orig_fields,
        array $tableStatus,
        string $engine,
        string $collation,
        $comment
    )
    {
        // From create.inc.php
        $values['fields'] = (array)$values['fields'];
        if ($values['auto_increment_col']) {
            $values['fields'][$values['auto_increment_col']]['auto_increment'] = true;
        }

        $fields = [];
        $all_fields = [];
        $use_all_fields = false;
        $foreign = [];
        $orig_field = \reset($orig_fields);
        $after = ' FIRST';

        $this->getForeignKeys();

        foreach ($values['fields'] as $key => $field) {
            $foreignKey = $this->foreignKeys[$field['type']] ?? null;
            //! can collide with user defined type
            $typeField = ($foreignKey !== null ? $this->referencableTables[$foreignKey] : $field);
            // Originally, deleted fields have the "field" field set to an empty string.
            // But in our implementation, the "field" field is deleted.
            // if($field['field'] != '')
            if (isset($field['field']) && $field['field'] != '') {
                if (!isset($field['has_default'])) {
                    $field['default'] = null;
                }
                $field['auto_increment'] = ($key == $values['auto_increment_col']);
                $field["null"] = isset($field["null"]);

                $process_field = $this->util->processField($field, $typeField);
                $all_fields[] = [$field['orig'], $process_field, $after];
                if (!$orig_field || $process_field != $this->util->processField($orig_field, $orig_field)) {
                    $fields[] = [$field['orig'], $process_field, $after];
                    if ($field['orig'] != '' || $after) {
                        $use_all_fields = true;
                    }
                }
                if ($foreignKey !== null) {
                    $foreign[$this->db->escapeId($field['field'])] = ($table != '' && $this->db->jush() != 'sqlite' ? 'ADD' : ' ') .
                        $this->db->formatForeignKey([
                            'table' => $this->foreignKeys[$field['type']],
                            'source' => [$field['field']],
                            'target' => [$typeField['field']],
                            'on_delete' => $field['on_delete'],
                        ]);
                }
                $after = ' AFTER ' . $this->db->escapeId($field['field']);
            } elseif ($field['orig'] != '') {
                // A missing "field" field and a not empty "orig" field means the column is to be dropped.
                // We also append null in the array because the drivers code accesses field at position 1.
                $use_all_fields = true;
                $fields[] = [$field['orig'], null];
            }
            if ($field['orig'] != '') {
                $orig_field = \next($orig_fields);
                if (!$orig_field) {
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
        //             $partitions[] = "\n  PARTITION " . $this->db->escapeId($val) .
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
        // elseif($this->db->support('partitioning') &&
        //     \preg_match('~partitioned~', $tableStatus['Create_options']))
        // {
        //     $partitioning .= "\nREMOVE PARTITIONING";
        // }

        $name = \trim($values['name']);
        $autoIncrement = $this->util->number($this->util->input()->getAutoIncrementStep());
        if ($this->db->jush() == 'sqlite' && ($use_all_fields || $foreign)) {
            $fields = $all_fields;
        }

        $success = $this->db->alterTable(
            $table,
            $name,
            $fields,
            $foreign,
            $comment,
            $engine,
            $collation,
            $autoIncrement,
            $partitioning
        );

        $message = $table == '' ?
            $this->util->lang('Table has been created.') :
            $this->util->lang('Table has been altered.');

        $error = $this->util->error();

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
        $tableStatus = [];

        $comment = $values['comment'] ?? null;
        $engine = $values['engine'] ?? '';
        $collation = $values['collation'] ?? '';

        return $this->createOrAlterTable(
            $values,
            '',
            $orig_fields,
            $tableStatus,
            $engine,
            $collation,
            $comment
        );
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
        $orig_fields = $this->db->fields($table);
        $tableStatus = $this->db->tableStatus($table);
        if (!$tableStatus) {
            throw new Exception($this->util->lang('No tables.'));
        }

        $currComment = $tableStatus['Comment'] ?? null;
        $currEngine = $tableStatus['Engine'] ?? '';
        $currCollation = $tableStatus['Collation'] ?? '';
        $comment = $values['comment'] != $currComment ? $values['comment'] : null;
        $engine = $values['engine'] != $currEngine ? $values['engine'] : '';
        $collation = $values['collation'] != $currCollation ? $values['collation'] : '';

        return $this->createOrAlterTable(
            $values,
            $table,
            $orig_fields,
            $tableStatus,
            $engine,
            $collation,
            $comment
        );
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
        $success = $this->db->dropTables([$table]);

        $error = $this->util->error();

        $message = $this->util->lang('Table has been dropped.');

        return \compact('success', 'message', 'error');
    }
}
