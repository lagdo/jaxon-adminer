<?php

namespace Lagdo\Adminer\DbAdmin;

use Exception;

/**
 * Admin user functions
 */
class UserAdmin extends AbstractAdmin
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
        // From user.inc.php
        $grants = [];

        //! use information_schema for MySQL 5 - column names in column privileges are not escaped
        if (($result = $this->db->query("SHOW GRANTS FOR " .
            $this->db->quote($user) . "@" . $this->db->quote($host)))) {
            while ($row = $result->fetch_row()) {
                if (\preg_match('~GRANT (.*) ON (.*) TO ~', $row[0], $match) &&
                    \preg_match_all('~ *([^(,]*[^ ,(])( *\([^)]+\))?~', $match[1], $matches, PREG_SET_ORDER)) { //! escape the part between ON and TO
                    foreach ($matches as $val) {
                        $match2 = $match[2] ?? '';
                        $val2 = $val[2] ?? '';
                        if ($val[1] != "USAGE") {
                            $grants["$match2$val2"][$val[1]] = true;
                        }
                        if (\preg_match('~ WITH GRANT OPTION~', $row[0])) { //! don't check inside strings and identifiers
                            $grants["$match2$val2"]["GRANT OPTION"] = true;
                        }
                    }
                }
                if (\preg_match("~ IDENTIFIED BY PASSWORD '([^']+)~", $row[0], $match)) {
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
        foreach ($this->db->get_rows("SHOW PRIVILEGES") as $row) {
            // Context of "Grant option" privilege is set to empty string
            $contexts = \explode(",", ($row["Privilege"] == "Grant option" ? "" : $row["Context"]));
            foreach ($contexts as $context) {
                $features[$context][$row["Privilege"]] = $row["Comment"];
            }
        }

        // Privileges of "Server Admin" and "File access on server" are merged
        $features["Server Admin"] = \array_merge(
            $features["Server Admin"],
            $features["File access on server"]
        );
        // Comment for this is "No privileges - allow connect only"
        unset($features["Server Admin"]["Usage"]);

        if (\array_key_exists("Create routine", $features["Procedures"])) {
            // MySQL bug #30305
            $features["Databases"]["Create routine"] = $features["Procedures"]["Create routine"];
            unset($features["Procedures"]["Create routine"]);
        }

        $features["Columns"] = [];
        foreach (["Select", "Insert", "Update", "References"] as $val) {
            $features["Columns"][$val] = $features["Tables"][$val];
        }

        foreach ($features["Tables"] as $key => $val) {
            unset($features["Databases"][$key]);
        }

        $privileges = [];
        $contexts = [
            "" => "",
            "Server Admin" => $this->util->lang('Server'),
            "Databases" => $this->util->lang('Database'),
            "Tables" => $this->util->lang('Table'),
            "Columns" => $this->util->lang('Column'),
            "Procedures" => $this->util->lang('Routine'),
        ];
        foreach ($contexts as $context => $desc) {
            foreach ((array)$features[$context] as $privilege => $comment) {
                $detail = [
                    $desc,
                    $this->util->h($privilege),
                ];
                // echo "<tr><td" . ($desc ? ">$desc<td" : " colspan='2'") .
                //     ' lang="en" title="' . h($comment) . '">' . h($privilege);
                $i = 0;
                foreach ($grants as $object => $grant) {
                    $name = "'grants[$i][" . $this->util->h(strtoupper($privilege)) . "]'";
                    $value = $grant[\strtoupper($privilege)] ?? false;
                    if ($context == "Server Admin" && $object != (isset($grants["*.*"]) ? "*.*" : ".*")) {
                        $detail[] = '';
                    }
                    // elseif(isset($values["grant"]))
                    // {
                    //     $detail[] = "<select name=$name><option><option value='1'" .
                    //         ($value ? " selected" : "") . ">" . $this->util->lang('Grant') .
                    //         "<option value='0'" . ($value == "0" ? " selected" : "") . ">" .
                    //         $this->util->lang('Revoke') . "</select>";
                    // }
                    else {
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
        $main_actions = [
            'add-user' => $this->util->lang('Create user'),
        ];

        $headers = [
            $this->util->lang('Username'),
            $this->util->lang('Server'),
            '',
            '',
        ];

        // From privileges.inc.php
        $result = $this->db->query("SELECT User, Host FROM mysql." .
            ($database == "" ? "user" : "db WHERE " . $this->db->quote($database) . " LIKE Db") .
            " ORDER BY Host, User");
        $grant = $result;
        if (!$result) {
            // list logged user, information_schema.USER_PRIVILEGES lists just the current user too
            $result = $this->db->query("SELECT SUBSTRING_INDEX(CURRENT_USER, '@', 1) " .
                "AS User, SUBSTRING_INDEX(CURRENT_USER, '@', -1) AS Host");
        }
        $details = [];
        while ($row = $result->fetch_assoc()) {
            $details[] = [
                'user' => $this->util->h($row["User"]),
                'host' => $this->util->h($row["Host"]),
            ];
        }

        // Fetch user grants
        foreach ($details as &$detail) {
            $grants = $this->fetchUserGrants($detail['user'], $detail['host']);
            $detail['grants'] = \array_keys($grants);
        }

        return \compact('headers', 'details', 'main_actions');
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
            $this->util->lang('Contexts'),
            $this->util->lang('Privileges'),
        ];
        $i = 0;
        foreach ($grants as $object => $grant) {
            //! separate db, table, columns, PROCEDURE|FUNCTION, routine
            $headers[] = $object === '*.*' ?
                '<input type="hidden" name="objects[' . $i . ']" value="*.*" />*.*' :
                '<input name="objects[' . $i . ']" value="' . $this->util->h($object) . '" autocapitalize="off" />';
            $i++;
        }

        $main_actions = [];

        $user = [
            'host' => [
                'label' => $this->util->lang('Server'),
                'value' => '',
            ],
            'name' => [
                'label' => $this->util->lang('Username'),
                'value' => '',
            ],
            'pass' => [
                'label' => $this->util->lang('Password'),
                'value' => '',
            ],
            'hashed' => [
                'label' => $this->util->lang('Hashed'),
                'value' => false,
            ],
        ];

        $details = $this->fetchUserPrivileges($grants);

        return \compact('user', 'headers', 'details', 'main_actions');
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
        if ($database !== '') {
            $grants = \array_key_exists($database, $grants) ? [$database => $grants[$database]] : [];
        }

        $headers = [
            $this->util->lang('Contexts'),
            $this->util->lang('Privileges'),
        ];
        $i = 0;
        foreach ($grants as $object => $grant) {
            //! separate db, table, columns, PROCEDURE|FUNCTION, routine
            $headers[] = $object === '*.*' ?
                '<input type="hidden" name="objects[' . $i . ']" value="*.*" />*.*' :
                '<input name="objects[' . $i . ']" value="' . $this->util->h($object) . '" autocapitalize="off" />';
            $i++;
        }

        $main_actions = [];

        $user = [
            'host' => [
                'label' => $this->util->lang('Server'),
                'value' => $host,
            ],
            'name' => [
                'label' => $this->util->lang('Username'),
                'value' => $user,
            ],
            'pass' => [
                'label' => $this->util->lang('Password'),
                'value' => $this->password,
            ],
            'hashed' => [
                'label' => $this->util->lang('Hashed'),
                'value' => ($this->password != ''),
            ],
        ];

        $details = $this->fetchUserPrivileges($grants);

        return \compact('user', 'headers', 'details', 'main_actions');
    }
}
