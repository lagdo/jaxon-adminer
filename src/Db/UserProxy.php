<?php

namespace Lagdo\Adminer\Db;

use Exception;

/**
 * Proxy to calls to the Adminer functions
 */
class UserProxy
{
    /**
     * The user password
     *
     * @var string
     */
    protected $password = '';

    /**
     * Get the grants of a user on a given host
     *
     * @param string $user      The user name
     * @param string $host      The host name
     *
     * @return array
     */
    protected function fetchUserGrants($user = '', $host = '')
    {
        global $connection;

        // From user.inc.php
        $grants = [];

        //! use information_schema for MySQL 5 - column names in column privileges are not escaped
        if(($result = $connection->query("SHOW GRANTS FOR " .
            \adminer\q($user) . "@" . \adminer\q($host))))
        {
            while($row = $result->fetch_row())
            {
                if(\preg_match('~GRANT (.*) ON (.*) TO ~', $row[0], $match) &&
                    \preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~', $match[1], $matches, PREG_SET_ORDER))
                { //! escape the part between ON and TO
                    foreach($matches as $val)
                    {
                        $match2 = $match[2] ?? '';
                        $val2 = $val[2] ?? '';
                        if($val[1] != "USAGE")
                        {
                            $grants["$match2$val2"][$val[1]] = true;
                        }
                        if(\preg_match('~ WITH GRANT OPTION~', $row[0]))
                        { //! don't check inside strings and identifiers
                            $grants["$match2$val2"]["GRANT OPTION"] = true;
                        }
                    }
                }
                if(\preg_match("~ IDENTIFIED BY PASSWORD '([^']+)~", $row[0], $match))
                {
                    $this->password = $match[1];
                }
            }
        }

        return $grants;
    }

    /**
     * Get the user privileges
     *
     * @param array $grants     The user grants
     *
     * @return array
     */
    protected function fetchUserPrivileges(array $grants)
    {
        // From user.inc.php
        $features = [
            "" => [
                "All privileges" => "",
            ],
        ];
        foreach(\adminer\get_rows("SHOW PRIVILEGES") as $row)
        {
            // Context of "Grant option" privilege is set to empty string
            $contexts = \explode(",", ($row["Privilege"] == "Grant option" ? "" : $row["Context"]));
            foreach($contexts as $context)
            {
                $features[$context][$row["Privilege"]] = $row["Comment"];
            }
        }

        // Privileges of "Server Admin" and "File access on server" are merged
        $features["Server Admin"] = \array_merge($features["Server Admin"],
            $features["File access on server"]);
        // Comment for this is "No privileges - allow connect only"
        unset($features["Server Admin"]["Usage"]);

        if(\array_key_exists("Create routine", $features["Procedures"]))
        {
            // MySQL bug #30305
            $features["Databases"]["Create routine"] = $features["Procedures"]["Create routine"];
            unset($features["Procedures"]["Create routine"]);
        }

        $features["Columns"] = [];
        foreach(["Select", "Insert", "Update", "References"] as $val)
        {
            $features["Columns"][$val] = $features["Tables"][$val];
        }

        foreach($features["Tables"] as $key => $val)
        {
            unset($features["Databases"][$key]);
        }

        $privileges = [];
        $contexts = [
            "" => "",
            "Server Admin" => \adminer\lang('Server'),
            "Databases" => \adminer\lang('Database'),
            "Tables" => \adminer\lang('Table'),
            "Columns" => \adminer\lang('Column'),
            "Procedures" => \adminer\lang('Routine'),
        ];
        foreach($contexts as $context => $desc)
        {
            foreach((array)$features[$context] as $privilege => $comment)
            {
                $detail = [
                    $desc,
                    \adminer\h($privilege),
                ];
                // echo "<tr><td" . ($desc ? ">$desc<td" : " colspan='2'") .
                //     ' lang="en" title="' . h($comment) . '">' . h($privilege);
                $i = 0;
                foreach($grants as $object => $grant)
                {
                    $name = "'grants[$i][" . \adminer\h(strtoupper($privilege)) . "]'";
                    $value = $grant[\strtoupper($privilege)] ?? false;
                    if ($context == "Server Admin" && $object != (isset($grants["*.*"]) ? "*.*" : ".*"))
                    {
                        $detail[] = '';
                    }
                    // elseif(isset($_GET["grant"]))
                    // {
                    //     $detail[] = "<select name=$name><option><option value='1'" .
                    //         ($value ? " selected" : "") . ">" . \adminer\lang('Grant') .
                    //         "<option value='0'" . ($value == "0" ? " selected" : "") . ">" .
                    //         \adminer\lang('Revoke') . "</select>";
                    // }
                    else
                    {
                        // $detail[] = "<label class='block'><input type='checkbox' name=$name value='1'" .
                        //     ($value ? " checked" : "") . ($privilege == "All privileges" ? " id='grants-$i-all'>"
                        //     //! uncheck all except grant if all is checked
                        //     : ">" . ($privilege == "Grant option" ? "" :
                        //     \adminer\script("qsl('input').onclick = function ()" .
                        //     "{ if (this.checked) formUncheck('grants-$i-all'); };"))) . "</label>";
                        $detail[] = "<input type='checkbox' name=$name" . ($value ? " checked />" : " />");
                    }
                    $i++;
                }
                $privileges[] = $detail;
            }
        }

        return $privileges;
    }

    /**
     * Get the privilege list
     * This feature is available only for MySQL
     *
     * @param string $database  The database name
     *
     * @return array
     */
    public function getPrivileges($database = '')
    {
        global $connection;

        $actions = [
            'add-user' => \adminer\lang('Create user'),
        ];

        $headers = [
            \adminer\lang('Username'),
            \adminer\lang('Server'),
            '',
            '',
        ];

        // From privileges.inc.php
        $result = $connection->query("SELECT User, Host FROM mysql." .
            ($database == "" ? "user" : "db WHERE " . q($database) . " LIKE Db") .
            " ORDER BY Host, User");
        $grant = $result;
        if(!$result) {
            // list logged user, information_schema.USER_PRIVILEGES lists just the current user too
            $result = $connection->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) " .
                "AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");
        }
        $details = [];
        while ($row = $result->fetch_assoc()) {
            $details[] = [
                'user' => \adminer\h($row["User"]),
                'host' => \adminer\h($row["Host"]),
            ];
        }

        // Fetch user grants
        foreach($details as &$detail)
        {
            $grants = $this->fetchUserGrants($detail['user'], $detail['host']);
            $detail['grants'] = \array_keys($grants);
        }

        return \compact('headers', 'details', 'actions');
    }

    /**
     * Get the grants of a user on a given host
     *
     * @return array
     */
    public function newUserPrivileges()
    {
        $grants = [".*" => []];

        $headers = [
            \adminer\lang('Contexts'),
            \adminer\lang('Privileges'),
        ];
        $i = 0;
        foreach($grants as $object => $grant)
        {
            //! separate db, table, columns, PROCEDURE|FUNCTION, routine
            $headers[] = $object === '*.*' ?
                '<input type="hidden" name="objects[' . $i . ']" value="*.*" />*.*' :
                '<input name="objects[' . $i . ']" value="' . \adminer\h($object) . '" autocapitalize="off" />';
            $i++;
        }

        $actions = [];

        $user = [
            'host' => [
                'label' => \adminer\lang('Server'),
                'value' => '',
            ],
            'name' => [
                'label' => \adminer\lang('Username'),
                'value' => '',
            ],
            'pass' => [
                'label' => \adminer\lang('Password'),
                'value' => '',
            ],
            'hashed' => [
                'label' => \adminer\lang('Hashed'),
                'value' => false,
            ],
        ];

        $details = $this->fetchUserPrivileges($grants);

        return \compact('user', 'headers', 'details', 'actions');
    }

    /**
     * Get the grants of a user on a given host
     *
     * @param string $user      The user name
     * @param string $host      The host name
     * @param string $database  The database name
     *
     * @return array
     */
    public function getUserPrivileges($user, $host, $database)
    {
        $grants = $this->fetchUserGrants($user, $host);
        if($database !== '')
        {
            $grants = \array_key_exists($database, $grants) ? [$database => $grants[$database]] : [];
        }

        $headers = [
            \adminer\lang('Contexts'),
            \adminer\lang('Privileges'),
        ];
        $i = 0;
        foreach($grants as $object => $grant)
        {
            //! separate db, table, columns, PROCEDURE|FUNCTION, routine
            $headers[] = $object === '*.*' ?
                '<input type="hidden" name="objects[' . $i . ']" value="*.*" />*.*' :
                '<input name="objects[' . $i . ']" value="' . \adminer\h($object) . '" autocapitalize="off" />';
            $i++;
        }

        $actions = [];

        $user = [
            'host' => [
                'label' => \adminer\lang('Server'),
                'value' => $host,
            ],
            'name' => [
                'label' => \adminer\lang('Username'),
                'value' => $user,
            ],
            'pass' => [
                'label' => \adminer\lang('Password'),
                'value' => $this->password,
            ],
            'hashed' => [
                'label' => \adminer\lang('Hashed'),
                'value' => ($this->password != ''),
            ],
        ];

        $details = $this->fetchUserPrivileges($grants);

        return \compact('user', 'headers', 'details', 'actions');
    }
}
