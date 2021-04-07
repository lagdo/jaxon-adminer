<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class ExportProxy
{
    /**
     * Get data for export
     *
     * @param string $database      The database name
     *
     * @return array
     */
    public function getExportOptions(string $database)
    {
        global $adminer, $jush;

        $db_style = ['', 'USE', 'DROP+CREATE', 'CREATE'];
        $table_style = ['', 'DROP+CREATE', 'CREATE'];
        $data_style = ['', 'TRUNCATE+INSERT', 'INSERT'];
        if($jush == 'sql')
        { //! use insertUpdate() in all drivers
            $data_style[] = 'INSERT+UPDATE';
        }
        // \parse_str($_COOKIE['adminer_export'], $row);
        // if (!$row) {
            $row = [
                'output' => 'text',
                'format' => 'sql',
                'db_style' => ($database != '' ? '' : 'CREATE'),
                'table_style' => 'DROP+CREATE',
                'data_style' => 'INSERT',
            ];
        // }
        // if (!isset($row['events'])) { // backwards compatibility
            $row['routines'] = $row['events'] = true; // ($_GET['dump'] == '');
            $row['triggers'] = $row['table_style'];
        // }
        $options = [
            'output' => [
                'label' => \adminer\lang('Output'),
                'options' => $adminer->dumpOutput(),
                'value' => $row['output'],
            ],
            'format' => [
                'label' => \adminer\lang('Format'),
                'options' => $adminer->dumpFormat(),
                'value' => $row['format'],
            ],
            'table_style' => [
                'label' => \adminer\lang('Tables'),
                'options' => $table_style,
                'value' => $row['table_style'],
            ],
            'auto_increment' => [
                'label' => \adminer\lang('Auto Increment'),
                'value' => 1,
                'checked' => $row['auto_increment'] ?? false,
            ],
            'data_style' => [
                'label' => \adminer\lang('Data'),
                'options' => $data_style,
                'value' => $row['data_style'],
            ],
        ];
        if($jush !== 'sqlite')
        {
            $options['db_style'] = [
                'label' => \adminer\lang('Database'),
                'options' => $db_style,
                'value' => $row['db_style'],
            ];
            if(\adminer\support('routine'))
            {
                $options['routines'] = [
                    'label' => \adminer\lang('Routines'),
                    'value' => 1,
                    'checked' => $row['routines'],
                ];
            }
            if(\adminer\support('event'))
            {
                $options['events'] = [
                    'label' => \adminer\lang('Events'),
                    'value' => 1,
                    'checked' => $row['events'],
                ];
            }
        }
        if(\adminer\support('trigger'))
        {
            $options['triggers'] = [
                'label' => \adminer\lang('Triggers'),
                'value' => 1,
                'checked' => $row['triggers'],
            ];
        }

        $TABLE = '';
        $results = [
            'options' => $options,
            'prefixes' => [],
        ];
        if(($database))
        {
            $tables = [
                'headers' => [\adminer\lang('Tables'), \adminer\lang('Data')],
                'details' => [],
            ];
            $tables_list = \adminer\tables_list();
            foreach($tables_list as $name => $type)
            {
                $prefix = \preg_replace('~_.*~', '', $name);
                //! % may be part of table name
                $checked = ($TABLE == "" || $TABLE == (\substr($TABLE, -1) == "%" ? "$prefix%" : $name));
                // $results['prefixes'][$prefix]++;

                $tables['details'][] = \compact('name', 'type', 'prefix', 'checked');
            }
            $results['tables'] = $tables;
        }
        else
        {
            $databases = [
                'headers' => [\adminer\lang('Database')],
                'details' => [],
            ];
            $databases_list = $adminer->databases(false) ?? [];
            foreach($databases_list as $name)
            {
                if(!\adminer\information_schema($name))
                {
                    $prefix = \preg_replace('~_.*~', '', $name);
                    // $results['prefixes'][$prefix]++;

                    $databases['details'][] = \compact('prefix', 'name');
                }
            }
            $results['databases'] = $databases;
        }

        $results['options'] = $options;
        $results['submit'] = \adminer\lang('Export');
        return $results;
    }
}
