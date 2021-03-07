<?php
// Vars
global $VERSION, $adminer;
?>

<div id="menu">
    <h1>
        <?php echo $adminer->name(); ?>
        <span class="version"><?php echo $VERSION; ?></span>
        <a href="https://www.adminer.org/#download"<?php echo target_blank(); ?> id="version">
            <?php echo (version_compare($VERSION, $_COOKIE["adminer_version"]) < 0 ? h($_COOKIE["adminer_version"]) : ""); ?>
        </a>
    </h1>

<?php include($missing == "auth" ? './logins.php' : './database.php'); ?>

</div>
