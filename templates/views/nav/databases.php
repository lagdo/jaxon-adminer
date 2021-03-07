<?php
// Vars
$_databases = [];
if ($missing == "auth") {
    foreach ((array) $_SESSION["pwds"] as $vendor => $servers) {
        foreach ($servers as $server => $usernames) {
            foreach ($usernames as $username => $password) {
                if ($password !== null) {
                    $dbs = $_SESSION["db"][$vendor][$server][$username];
                    foreach (($dbs ? array_keys($dbs) : array("")) as $db) {
                        $url = h(auth_url($vendor, $server, $username, $db));
                        $title = $username;
                        if($server != "")
                        {
                            $title .= "@" . h($server);
                        }
                        if($db != "")
                        {
                            $title .= " - $db";
                        }
                        $_databases[] = ['url' => $url, 'title' => $title];
                    }
                }
            }
        }
    }
}
?>

<?php if(count($_databases) > 0): ?>
    <ul id="logins">
    <?php foreach($_databases as $_database): ?>
        <li><a href="<?php echo $_database['url']; ?>"><?php echo $_database['title']; ?></a>
    <?php endforeach ?>
    </ul>
    <?php echo script("mixin(qs('#logins'), {onmouseover: menuOver, onmouseout: menuOut});"); ?>
<?php endif ?>
