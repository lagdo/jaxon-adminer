<?php

namespace Lagdo\Adminer\Db;

use Lagdo\DbAdmin\Driver\UtilInterface;
use Lagdo\Adminer\Exception\DbException;
use Lagdo\Adminer\Translator;

class Util implements UtilInterface
{
    /**
     * @var Db
     */
    public $db;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var Input
     */
    public $input;

    /**
     * The constructor
     *
     * @param Db $db
     * @param Translator $translator
     */
    public function __construct(Db $db, Translator $translator)
    {
        $this->db = $db;
        $this->translator = $translator;
        $this->input = new Input();
    }

    /**
     * Get a target="_blank" attribute
     * @return string
     */
    public function blankTarget()
    {
        return ' target="_blank" rel="noreferrer noopener"';
    }

    /**
     * Name in title and navigation
     * @return string HTML code
     */
    public function name()
    {
        return "<a href='https://www.adminer.org/'" . $this->blankTarget() . " id='h1'>Adminer</a>";
    }

    /**
     * @inheritDoc
     */
    public function input()
    {
        return $this->input;
    }

    /**
     * @inheritDoc
     */
    public function lang($idf)
    {
        return \call_user_func_array([$this->translator, "lang"], \func_get_args());
    }

    /**
     * @inheritDoc
     */
    public function html($string)
    {
        return \str_replace("\0", "&#0;", \htmlspecialchars($string, ENT_QUOTES, 'utf-8'));
    }

    /**
     * @inheritDoc
     */
    public function number($val)
    {
        return preg_replace('~[^0-9]+~', '', $val);
    }

    /**
     * @inheritDoc
     */
    public function isUtf8($val)
    {
        // don't print control chars except \t\r\n
        return (preg_match('~~u', $val) && !preg_match('~[\0-\x8\xB\xC\xE-\x1F]~', $val));
    }

    /**
     * Get escaped error message
     *
     * @return string
     */
    public function error()
    {
        return $this->html($this->db->error());
    }

    /**
     * Check whether the string is e-mail address
     * @param string
     * @return bool
     */
    public function isMail($email)
    {
        $atom = '[-a-z0-9!#$%&\'*+/=?^_`{|}~]'; // characters of local-name
        $domain = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])'; // one domain component
        $pattern = "$atom+(\\.$atom+)*@($domain?\\.)+$domain";
        return is_string($email) && preg_match("(^$pattern(,\\s*$pattern)*\$)i", $email);
    }

    /**
     * Check whether the string is URL address
     * @param string
     * @return bool
     */
    public function isUrl($string)
    {
        $domain = '[a-z0-9]([-a-z0-9]{0,61}[a-z0-9])'; // one domain component //! IDN
        //! restrict path, query and fragment characters
        return preg_match("~^(https?)://($domain?\\.)+$domain(:\\d+)?(/.*)?(\\?.*)?(#.*)?\$~i", $string);
    }

    /**
     * Check if field should be shortened
     * @param array
     * @return bool
     */
    public function isShortable($field)
    {
        return preg_match('~char|text|json|lob|geometry|point|linestring|polygon|string|bytea~', $field["type"] ?? '');
    }

    /**
     * @inheritDoc
     */
    public function iniBool($ini)
    {
        $val = ini_get($ini);
        return (preg_match('~^(on|true|yes)$~i', $val) || (int) $val); // boolean values set by php_value are strings
    }

    /**
     * Get INI bytes value
     * @param string
     * @return int
     */
    public function iniBytes($ini)
    {
        $val = ini_get($ini);
        $unit = strtolower(substr($val, -1)); // Get the last char
        $ival = intval(substr($val, 0, -1)); // Remove the last char
        switch ($unit) {
            case 'g': $val = $ival * 1024 * 1024 * 1024; break;
            case 'm': $val = $ival * 1024 * 1024; break;
            case 'k': $val = $ival * 1024; break;
        }
        return $val;
    }

    /**
     * Format elapsed time
     * @param float output of microtime(true)
     * @return string HTML code
     */
    public function formatTime($start)
    {
        return $this->lang('%.3f s', max(0, microtime(true) - $start));
    }

    /**
     * Format decimal number
     * @param int
     * @return string
     */
    public function formatNumber($val)
    {
        return $this->translator->formatNumber($val);
    }

    /**
     * @inheritDoc
     */
    public function convertEolToHtml($string)
    {
        return str_replace("\n", "<br>", $string); // nl2br() uses XHTML before PHP 5.3
    }

    /**
     * Escape column key used in where()
     * @param string
     * @return string
     */
    public function escapeKey($key)
    {
        if (preg_match('(^([\w(]+)(' .
            str_replace("_", ".*", preg_quote($this->db->escapeId("_"))) . ')([ \w)]+)$)', $key, $match)) {
            //! columns looking like functions
            return $match[1] . $this->db->escapeId($this->db->unescapeId($match[2])) . $match[3]; //! SQL injection
        }
        return $this->db->escapeId($key);
    }

    /**
     * Create SQL condition from parsed query string
     * @param array $where parsed query string
     * @param array $fields
     * @return string
     */
    public function where($where, $fields = [])
    {
        $return = [];
        $wheres = $where["where"] ?? [];
        foreach ((array) $wheres as $key => $val) {
            $key = $this->bracketEscape($key, 1); // 1 - back
            $column = $this->escapeKey($key);
            $return[] = $column .
                // LIKE because of floats but slow with ints
                ($this->db->jush() == "sql" && is_numeric($val) && preg_match('~\.~', $val) ? " LIKE " .
                $this->db->quote($val) : ($this->db->jush() == "mssql" ? " LIKE " .
                $this->db->quote(preg_replace('~[_%[]~', '[\0]', $val)) : " = " . // LIKE because of text
                $this->db->unconvertField($fields[$key], $this->db->quote($val)))); //! enum and set
            if ($this->db->jush() == "sql" &&
                preg_match('~char|text~', $fields[$key]["type"]) && preg_match("~[^ -@]~", $val)) {
                // not just [a-z] to catch non-ASCII characters
                $return[] = "$column = " . $this->db->quote($val) . " COLLATE " . $this->db−>charset() . "_bin";
            }
        }
        $nulls = $where["null"] ?? [];
        foreach ((array) $nulls as $key) {
            $return[] = $this->escapeKey($key) . " IS NULL";
        }
        return implode(" AND ", $return);
    }

    /**
     * @inheritDoc
     */
    public function getFieldsFromEdit()
    {
        $return = [];
        $values = $this->input->values;
        foreach ((array) $values["field_keys"] as $key => $val) {
            if ($val != "") {
                $val = $this->bracketEscape($val);
                $values["function"][$val] = $values["field_funs"][$key];
                $values["fields"][$val] = $values["field_vals"][$key];
            }
        }
        foreach ((array) $values["fields"] as $key => $val) {
            $name = $this->bracketEscape($key, 1); // 1 - back
            $return[$name] = array(
                "field" => $name,
                "privileges" => array("insert" => 1, "update" => 1),
                "null" => 1,
                "auto_increment" => ($key == $this->db->primaryIdName()),
            );
        }
        return $return;
    }

    /**
     * Create repeat pattern for preg
     * @param string
     * @param int
     * @return string
     */
    public function repeatPattern($pattern, $length)
    {
        // fix for Compilation failed: number too big in {} quantifier
        // can create {0,0} which is OK
        return str_repeat("$pattern{0,65535}", $length / 65535) . "$pattern{0," . ($length % 65535) . "}";
    }

    /**
     * Shorten UTF-8 string
     * @param string
     * @param int
     * @param string
     * @return string escaped string with appended ...
     */
    public function shortenUtf8($string, $length = 80, $suffix = "")
    {
        if (!preg_match("(^(" . $this->repeatPattern("[\t\r\n -\x{10FFFF}]", $length) . ")($)?)u", $string, $match)) {
            // ~s causes trash in $match[2] under some PHP versions, (.|\n) is slow
            preg_match("(^(" . $this->repeatPattern("[\t\r\n -~]", $length) . ")($)?)", $string, $match);
        }
        return $this->html($match[1]) . $suffix . (isset($match[2]) ? "" : "<i>…</i>");
    }

    /**
     * Escape or unescape string to use inside form []
     * @param string
     * @param bool
     * @return string
     */
    public function bracketEscape($idf, $back = false)
    {
        // escape brackets inside name="x[]"
        static $trans = array(':' => ':1', ']' => ':2', '[' => ':3', '"' => ':4');
        return strtr($idf, ($back ? array_flip($trans) : $trans));
    }

    /**
     * Find unique identifier of a row
     * @param array
     * @param array result of indexes()
     * @return array or null if there is no unique identifier
     */
    public function uniqueArray($row, $indexes)
    {
        foreach ($indexes as $index) {
            if (preg_match("~PRIMARY|UNIQUE~", $index["type"])) {
                $return = [];
                foreach ($index["columns"] as $key) {
                    if (!isset($row[$key])) { // NULL is ambiguous
                        continue 2;
                    }
                    $return[$key] = $row[$key];
                }
                return $return;
            }
        }
    }

    /**
     * Table caption used in navigation and headings
     * @param array result of SHOW TABLE STATUS
     * @return string HTML code, "" to ignore table
     */
    public function tableName($tableStatus)
    {
        return $this->html($tableStatus["Name"]);
    }

    /**
     * Field caption used in select and edit
     * @param array single field returned from fields()
     * @param int order of column in select
     * @return string HTML code, "" to ignore field
     */
    public function fieldName($field, $order = 0)
    {
        return '<span title="' . $this->html($field["full_type"]) . '">' . $this->html($field["field"]) . '</span>';
    }

    /**
     * Returns export format options
     * @return array empty to disable export
     */
    public function dumpFormat()
    {
        return array('sql' => 'SQL', 'csv' => 'CSV,', 'csv;' => 'CSV;', 'tsv' => 'TSV');
    }

    /**
     * Returns export output options
     * @return array
     */
    public function dumpOutput()
    {
        $return = array('text' => $this->lang('open'), 'file' => $this->lang('save'));
        if (function_exists('gzencode')) {
            $return['gz'] = 'gzip';
        }
        return $return;
    }

    /**
     * Set the path of the file for webserver load
     * @return string path of the sql dump file
     */
    public function importServerPath()
    {
        return "adminer.sql";
    }

    /**
     * Export database structure
     * @param string
     * @return null prints data
     */
    // public function dumpDatabase($db) {
    // }

    /**
     * Get referencable tables with single column primary key except self
     * @param string
     * @return array ($tableName => $field)
     */
    public function referencableTables($self)
    {
        $return = []; // table_name => field
        foreach ($this->db->tableStatus('', true) as $tableName => $table) {
            if ($tableName != $self && $this->db->supportForeignKeys($table)) {
                foreach ($this->db->fields($tableName) as $field) {
                    if (isset($field["primary"])) {
                        if (isset($return[$tableName])) { // multi column primary key
                            unset($return[$tableName]);
                            break;
                        }
                        $return[$tableName] = $field;
                    }
                }
            }
        }
        return $return;
    }

    /**
     * Print before edit form
     * @param string
     * @param array
     * @param mixed
     * @param bool
     * @return null
     */
    public function editRowPrint($table, $fields, $row, $update)
    {
    }

    /**
     * Functions displayed in edit form
     * @param array $field Single field from fields()
     * @return array
     */
    public function editFunctions(array $field)
    {
        $return = ($field["null"] ? "NULL/" : "");
        $update = isset($options["select"]) || $this->where([]);
        foreach ($this->db->editFunctions() as $key => $functions) {
            if (!$key || (!isset($options["call"]) && $update)) { // relative functions
                foreach ($functions as $pattern => $val) {
                    if (!$pattern || preg_match("~$pattern~", $field["type"])) {
                        $return .= "/$val";
                    }
                }
            }
            if ($key && !preg_match('~set|blob|bytea|raw|file|bool~', $field["type"])) {
                $return .= "/SQL";
            }
        }
        if ($field["auto_increment"] && !$update) {
            $return = $this->lang('Auto Increment');
        }
        return explode("/", $return);
    }

    /**
     * Get hint for edit field
     * @param string table name
     * @param array single field from fields()
     * @param string
     * @return string
     */
    public function editHint($table, $field, $value)
    {
        return "";
    }

    /**
     * Value printed in select table
     * @param string HTML-escaped value to print
     * @param string link to foreign key
     * @param array single field returned from fields()
     * @param array original value before applying editValue() and escaping
     * @return string
     */
    public function _selectValue($val, $link, $field, $original)
    {
        $type = $field["type"] ?? '';
        $return = ($val === null ? "<i>NULL</i>" :
            (preg_match("~char|binary|boolean~", $type) && !preg_match("~var~", $type) ?
            "<code>$val</code>" : $val));
        if (preg_match('~blob|bytea|raw|file~', $type) && !$this->isUtf8($val)) {
            $return = "<i>" . $this->lang('%d byte(s)', strlen($original)) . "</i>";
        }
        if (preg_match('~json~', $type)) {
            $return = "<code class='jush-js'>$return</code>";
        }
        return ($link ? "<a href='" . $this->html($link) . "'" .
            ($this->isUrl($link) ? $this->blankTarget() : "") . ">$return</a>" : $return);
    }

    /**
     * Get a link to use in select table
     * @param string raw value of the field
     * @param array single field returned from fields()
     * @return string or null to create the default link
     */
    public function selectLink($val, $field)
    {
    }

    /**
     * Value conversion used in select and edit
     * @param string
     * @param array single field returned from fields()
     * @return string
     */
    public function editValue($val, $field)
    {
        return $val;
    }

    /**
     * Print enum input field
     * @param string "radio"|"checkbox"
     * @param string
     * @param array
     * @param mixed int|string|array
     * @param string
     * @return null
     */
    // public function enum_input($type, $attrs, $field, $value, $empty = null)
    // {
    //     preg_match_all("~'((?:[^']|'')*)'~", $field["length"], $matches);
    //     $return = ($empty !== null ? "<label><input type='$type'$attrs value='$empty'" .
    //         ((is_array($value) ? in_array($empty, $value) : $value === 0) ? " checked" : "") .
    //         "><i>" . $this->lang('empty') . "</i></label>" : "");
    //     foreach ($matches[1] as $i => $val) {
    //         $val = stripcslashes(str_replace("''", "'", $val));
    //         $checked = (is_int($value) ? $value == $i+1 : (is_array($value) ? in_array($i+1, $value) : $value === $val));
    //         $return .= " <label><input type='$type'$attrs value='" . ($i+1) . "'" .
    //             ($checked ? ' checked' : '') . '>' . $this->util->html($adminer->editValue($val, $field)) . '</label>';
    //     }
    //     return $return;
    // }

    /**
     * Get options to display edit field
     * @param string $table table name
     * @param boolean $select
     * @param array $field single field from fields()
     * @param string $attrs attributes to use inside the tag
     * @param string $value
     * @return array
     */
    public function editInput($table, $select, $field, $attrs, $value)
    {
        if ($field["type"] !== "enum") {
            return [];
        }
        $return = [];
        if (($select)) {
            $return[] = "<label><input type='radio'$attrs value='-1' checked><i>" .
                $this->lang('original') . "</i></label> ";
        }
        if (($field["null"])) {
            $return[] = "<label><input type='radio'$attrs value=''" .
                ($value !== null || ($select) ? "" : " checked") . "><i>NULL</i></label> ";
        }

        // From functions.inc.php (function enum_input())
        $empty = 0; // 0 - empty
        $type = 'radio';
        $return[] = "<label><input type='$type'$attrs value='$empty'" .
            ((\is_array($value) ? \in_array($empty, $value) : $value === 0) ? " checked" : "") .
            "><i>" . $this->lang('empty') . "</i></label>";

        \preg_match_all("~'((?:[^']|'')*)'~", $field["length"], $matches);
        foreach ($matches[1] as $i => $val) {
            $val = \stripcslashes(\str_replace("''", "'", $val));
            $checked = (\is_int($value) ? $value == $i + 1 :
                (\is_array($value) ? \in_array($i+1, $value) : $value === $val));
            $return[] = "<label><input type='$type'$attrs value='" . ($i+1) . "'" .
                ($checked ? ' checked' : '') . '>' . $this->html($this->editValue($val, $field)) . '</label>';
        }

        return $return;
    }

    /**
     * Get file contents from $_FILES
     * @param string
     * @param bool
     * @return mixed int for error, string otherwise
     */
    private function getFile($key, $decompress = false)
    {
        $file = $_FILES[$key];
        if (!$file) {
            return null;
        }
        foreach ($file as $key => $val) {
            $file[$key] = (array) $val;
        }
        $return = '';
        foreach ($file["error"] as $key => $error) {
            if ($error) {
                return $error;
            }
            $name = $file["name"][$key];
            $tmp_name = $file["tmp_name"][$key];
            $content = file_get_contents($decompress && preg_match('~\.gz$~', $name) ?
                "compress.zlib://$tmp_name" : $tmp_name); //! may not be reachable because of open_basedir
            if ($decompress) {
                $start = substr($content, 0, 3);
                if (function_exists("iconv") && preg_match("~^\xFE\xFF|^\xFF\xFE~", $start, $regs)) {
                    // not ternary operator to save memory
                    $content = iconv("utf-16", "utf-8", $content);
                } elseif ($start == "\xEF\xBB\xBF") { // UTF-8 BOM
                    $content = substr($content, 3);
                }
                $return .= $content . "\n\n";
            } else {
                $return .= $content;
            }
        }
        //! support SQL files not ending with semicolon
        return $return;
    }

    /**
     * Filter length value including enums
     * @param string
     * @return string
     */
    public function processLength($length)
    {
        if (!$length) {
            return "";
        }
        $enumLength = $this->server->enumLength;
        return (preg_match("~^\\s*\\(?\\s*$enumLength(?:\\s*,\\s*$enumLength)*+\\s*\\)?\\s*\$~", $length) &&
            preg_match_all("~$enumLength~", $length, $matches) ? "(" . implode(",", $matches[0]) . ")" :
            preg_replace('~^[0-9].*~', '(\0)', preg_replace('~[^-0-9,+()[\]]~', '', $length))
        );
    }

    /**
     * Create SQL string from field type
     * @param array
     * @param string
     * @return string
     */
    public function processType($field, $collate = "COLLATE")
    {
        $values = [
            'unsigned' => $field["unsigned"] ?? null,
            'collation' => $field["collation"] ?? null,
        ];
        return " $field[type]" . $this->processLength($field["length"]) .
            (preg_match($this->db->numberRegex(), $field["type"]) &&
            in_array($values["unsigned"], $this->db->unsigned()) ?
            " $values[unsigned]" : "") . (preg_match('~char|text|enum|set~', $field["type"]) &&
            $values["collation"] ? " $collate " . $this->db->quote($values["collation"]) : "")
        ;
    }

    /**
     * Create SQL string from field
     * @param array basic field information
     * @param array information about field type
     * @return array array("field", "type", "NULL", "DEFAULT", "ON UPDATE", "COMMENT", "AUTO_INCREMENT")
     */
    public function processField($field, $typeField)
    {
        return array(
            $this->db->escapeId(trim($field["field"])),
            $this->processType($typeField),
            ($field["null"] ? " NULL" : " NOT NULL"), // NULL for timestamp
            $this->db->defaultValue($field),
            (preg_match('~timestamp|datetime~', $field["type"]) && $field["on_update"] ?
                " ON UPDATE $field[on_update]" : ""),
            ($this->db->support("comment") && $field["comment"] != "" ?
                " COMMENT " . $this->db->quote($field["comment"]) : ""),
            ($field["auto_increment"] ? $this->db->autoIncrement() : null),
        );
    }

    /**
     * Process edit input field
     * @param one field from fields()
     * @param array the user inputs
     * @return string or false to leave the original value
     */
    public function processInput($field, $inputs)
    {
        $idf = $this->bracketEscape($field["field"]);
        $function = $inputs["function"][$idf] ?? '';
        $value = $inputs["fields"][$idf];
        if ($field["type"] == "enum") {
            if ($value == -1) {
                return false;
            }
            if ($value == "") {
                return "NULL";
            }
            return +$value;
        }
        if ($field["auto_increment"] && $value == "") {
            return null;
        }
        if ($function == "orig") {
            return (preg_match('~^CURRENT_TIMESTAMP~i', $field["on_update"]) ?
                $this->db->escapeId($field["field"]) : false);
        }
        if ($function == "NULL") {
            return "NULL";
        }
        if ($field["type"] == "set") {
            return array_sum((array) $value);
        }
        if ($function == "json") {
            $function = "";
            $value = json_decode($value, true);
            if (!is_array($value)) {
                return false; //! report errors
            }
            return $value;
        }
        if (preg_match('~blob|bytea|raw|file~', $field["type"]) && $this->iniBool("file_uploads")) {
            $file = $this->getFile("fields-$idf");
            if (!is_string($file)) {
                return false; //! report errors
            }
            return $this->db->quoteBinary($file);
        }
        return $this->_processInput($field, $value, $function);
    }

    /**
     * Process columns box in select
     * @param array selectable columns
     * @param array
     * @return array (array(select_expressions), array(group_expressions))
     */
    public function processSelectColumns($columns, $indexes)
    {
        $select = []; // select expressions, empty for *
        $group = []; // expressions without aggregation - will be used for GROUP BY if an aggregation function is used
        foreach ((array) $this->input->values["columns"] as $key => $val) {
            if ($val["fun"] == "count" ||
                ($val["col"] != "" && (!$val["fun"] ||
                in_array($val["fun"], $this->db->functions()) ||
                in_array($val["fun"], $this->db->grouping())))) {
                $select[$key] = $this->db->applySqlFunction(
                    $val["fun"],
                    ($val["col"] != "" ? $this->db->escapeId($val["col"]) : "*")
                );
                if (!in_array($val["fun"], $this->db->grouping())) {
                    $group[] = $select[$key];
                }
            }
        }
        return array($select, $group);
    }

    /**
     * Process sent input
     * @param array single field from fields()
     * @param string
     * @param string
     * @return string expression to use in a query
     */
    private function _processInput($field, $value, $function = "")
    {
        if ($function == "SQL") {
            return $value; // SQL injection
        }
        $name = $field["field"];
        $return = $this->db->quote($value);
        if (preg_match('~^(now|getdate|uuid)$~', $function)) {
            $return = "$function()";
        } elseif (preg_match('~^current_(date|timestamp)$~', $function)) {
            $return = $function;
        } elseif (preg_match('~^([+-]|\|\|)$~', $function)) {
            $return = $this->db->escapeId($name) . " $function $return";
        } elseif (preg_match('~^[+-] interval$~', $function)) {
            $return = $this->db->escapeId($name) . " $function " .
                (preg_match("~^(\\d+|'[0-9.: -]') [A-Z_]+\$~i", $value) ? $value : $return);
        } elseif (preg_match('~^(addtime|subtime|concat)$~', $function)) {
            $return = "$function(" . $this->db->escapeId($name) . ", $return)";
        } elseif (preg_match('~^(md5|sha1|password|encrypt)$~', $function)) {
            $return = "$function($return)";
        }
        return $this->db->unconvertField($field, $return);
    }

    /**
     * Process search box in select
     * @param array
     * @param array
     * @return array expressions to join by AND
     */
    public function processSelectSearch($fields, $indexes)
    {
        $return = [];
        foreach ($indexes as $i => $index) {
            if ($index["type"] == "FULLTEXT" && $this->input->values["fulltext"][$i] != "") {
                $columns = array_map(function ($column) {
                    return $this->db->escapeId($column);
                }, $index["columns"]);
                $return[] = "MATCH (" . implode(", ", $columns) . ") AGAINST (" .
                    $this->db->quote($this->input->values["fulltext"][$i]) .
                    (isset($this->input->values["boolean"][$i]) ? " IN BOOLEAN MODE" : "") . ")";
            }
        }
        foreach ((array) $this->input->values["where"] as $key => $val) {
            if ("$val[col]$val[val]" != "" && in_array($val["op"], $this->db->operators())) {
                $prefix = "";
                $cond = " $val[op]";
                if (preg_match('~IN$~', $val["op"])) {
                    $in = $this->processLength($val["val"]);
                    $cond .= " " . ($in != "" ? $in : "(NULL)");
                } elseif ($val["op"] == "SQL") {
                    $cond = " $val[val]"; // SQL injection
                } elseif ($val["op"] == "LIKE %%") {
                    $cond = " LIKE " . $this->_processInput($fields[$val["col"]], "%$val[val]%");
                } elseif ($val["op"] == "ILIKE %%") {
                    $cond = " ILIKE " . $this->_processInput($fields[$val["col"]], "%$val[val]%");
                } elseif ($val["op"] == "FIND_IN_SET") {
                    $prefix = "$val[op](" . $this->db->quote($val["val"]) . ", ";
                    $cond = ")";
                } elseif (!preg_match('~NULL$~', $val["op"])) {
                    $cond .= " " . $this->_processInput($fields[$val["col"]], $val["val"]);
                }
                if ($val["col"] != "") {
                    $return[] = $prefix . $this->db->convertSearch(
                        $this->db->escapeId($val["col"]),
                        $val,
                        $fields[$val["col"]]
                    ) . $cond;
                } else {
                    // find anywhere
                    $cols = [];
                    foreach ($fields as $name => $field) {
                        if ((preg_match('~^[-\d.' . (preg_match('~IN$~', $val["op"]) ? ',' : '') . ']+$~', $val["val"]) ||
                            !preg_match('~' . $this->db->numberRegex() . '|bit~', $field["type"])) &&
                            (!preg_match("~[\x80-\xFF]~", $val["val"]) || preg_match('~char|text|enum|set~', $field["type"])) &&
                            (!preg_match('~date|timestamp~', $field["type"]) || preg_match('~^\d+-\d+-\d+~', $val["val"]))
                        ) {
                            $cols[] = $prefix . $this->db->convertSearch($this->db->escapeId($name), $val, $field) . $cond;
                        }
                    }
                    $return[] = ($cols ? "(" . implode(" OR ", $cols) . ")" : "1 = 0");
                }
            }
        }
        return $return;
    }

    /**
     * Process order box in select
     * @param array
     * @param array
     * @return array expressions to join by comma
     */
    public function processSelectOrder($fields, $indexes)
    {
        $return = [];
        foreach ((array) $this->input->values["order"] as $key => $val) {
            if ($val != "") {
                $regexp = '~^((COUNT\(DISTINCT |[A-Z0-9_]+\()(`(?:[^`]|``)+`|"(?:[^"]|"")+")\)|COUNT\(\*\))$~';
                $return[] = (preg_match($regexp, $val) ? $val : $this->db->escapeId($val)) . //! MS SQL uses []
                    (isset($this->input->values["desc"][$key]) ? " DESC" : "");
            }
        }
        return $return;
    }

    /**
     * Process limit box in select
     * @return string expression to use in LIMIT, will be escaped
     */
    public function processSelectLimit()
    {
        return (isset($this->input->values["limit"]) ? $this->input->values["limit"] : "50");
    }

    /**
     * Process length box in select
     * @return string number of characters to shorten texts, will be escaped
     */
    public function processSelectLength()
    {
        return (isset($this->input->values["text_length"]) ? $this->input->values["text_length"] : "100");
    }

    /**
     * Process extras in select form
     * @param array AND conditions
     * @param array
     * @return bool true if processed, false to process other parts of form
     */
    public function processSelectEmail($where, $foreignKeys)
    {
        return false;
    }

    /**
     * Query printed after execution in the message
     * @param string executed query
     * @param string elapsed time
     * @param bool
     * @return string
     */
    public function messageQuery($query, $time, $failed = false)
    {
        if (strlen($query) > 1e6) {
            // [\x80-\xFF] - valid UTF-8, \n - can end by one-line comment
            $query = preg_replace('~[\x80-\xFF]+$~', '', substr($query, 0, 1e6)) . "\n…";
        }
        return $query;
    }

    /**
     * Format value to use in select
     * @param string
     * @param string
     * @param array
     * @param int
     * @return string HTML
     */
    public function selectValue($val, $link, $field, $textLength)
    {
        if (is_array($val)) {
            $return = "";
            foreach ($val as $k => $v) {
                $return .= "<tr>" . ($val != array_values($val) ? "<th>" . $this->html($k) : "") . "<td>" .
                    $this->selectValue($v, $link, $field, $textLength);
            }
            return "<table cellspacing='0'>$return</table>";
        }
        if (!$link) {
            $link = $this->selectLink($val, $field);
        }
        if ($link === null) {
            if ($this->isMail($val)) {
                $link = "mailto:$val";
            }
            if ($this->isUrl($val)) {
                $link = $val; // IE 11 and all modern browsers hide referrer
            }
        }
        $return = $this->editValue($val, $field);
        if ($return !== null) {
            if (!$this->isUtf8($return)) {
                $return = "\0"; // htmlspecialchars of binary data returns an empty string
            } elseif ($textLength != "" && $this->isShortable($field)) {
                // usage of LEFT() would reduce traffic but complicate query -
                // expected average speedup: .001 s VS .01 s on local network
                $return = $this->shortenUtf8($return, max(0, +$textLength));
            } else {
                $return = $this->html($return);
            }
        }
        return $this->_selectValue($return, $link, $field, $val);
    }

    /**
     * Query printed in SQL command before execution
     * @param string query to be executed
     * @return string escaped query to be printed
     */
    public function sqlCommandQuery($query)
    {
        return $this->shortenUtf8(trim($query), 1000);
    }

    /**
     * Execute query and redirect if successful
     * @param string
     * @param string
     * @param string
     * @param bool
     * @param bool
     * @param bool
     * @param string
     * @return bool
     */
    public function queryAndRedirect($query, $location = null, $message = null,
        $redirect = false, $execute = true, $failed = false, $time = "")
    {
        if ($execute) {
            $start = microtime(true);
            $failed = !$this->db->query($query);
            $time = $this->formatTime($start);
        }
        $sql = "";
        if ($query) {
            $sql = $this->messageQuery($query, $time, $failed);
        }
        if ($failed) {
            throw new DbException($this->error() . $sql);
            // $error = $this->error() . $sql . script("messagesPrint();");
            // return false;
        }
        // if ($redirect) {
        //     redirect($location, $message . $sql);
        // }
        return true;
    }

    /**
     * Redirect by remembered queries
     * @param string
     * @param string
     * @param bool
     * @return bool
     */
    protected function queriesAndRedirect($location, $message, $redirect)
    {
        list($queries, $time) = $this->queries(null);
        return $this->queryAndRedirect($queries, $location, $message, $redirect, false, !$redirect, $time);
    }

    /**
     * Drop old object and create a new one
     * @param string $drop drop old object query
     * @param string $create create new object query
     * @param string $drop_created drop new object query
     * @param string $test create test object query
     * @param string $drop_test drop test object query
     * @param string $location
     * @param string $message_drop
     * @param string $message_alter
     * @param string $message_create
     * @param string $old_name
     * @param string $new_name
     * @return null redirect in success
     */
    public function dropAndCreate($drop, $create, $drop_created, $test, $drop_test,
        $location, $message_drop, $message_alter, $message_create, $old_name, $new_name)
    {
        if ($old_name == "") {
            $this->queryAndRedirect($drop, $location, $message_drop);
        } elseif ($old_name == "") {
            $this->queryAndRedirect($create, $location, $message_create);
        } elseif ($old_name != $new_name) {
            $created = $this->db->queries($create);
            $this->queriesAndRedirect($location, $message_alter, $created && $this->db->queries($drop));
            if ($created) {
                $this->db->queries($drop_created);
            }
        } else {
            $this->queriesAndRedirect($location, $message_alter,
                $this->db->queries($test) && $this->db->queries($drop_test) &&
                $this->db->queries($drop) && $this->db->queries($create));
        }
    }

    /**
     * Drop old object and redirect
     * @param string drop old object query
     * @param string
     * @param string
     * @return null redirect in success
     */
    public function drop($drop, $location, $message_drop)
    {
        return $this->queryAndRedirect($drop, $location, $message_drop);
    }
}
