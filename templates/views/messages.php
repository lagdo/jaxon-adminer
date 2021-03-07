<?php
// Vars
$uri = preg_replace('~^[^?]*~', '', $_SERVER["REQUEST_URI"]);
$messages = $_SESSION["messages"][$uri] ?: [];
unset($_SESSION["messages"][$uri]);
?>
<?php foreach($messages as $message): ?>
		<div class='message'><?php echo $message; ?></div>
<?php endforeach ?>
<?php echo script("messagesPrint();"); ?>
<?php if($error): ?>
        <div class='error'><?php echo $error; ?></div>
<?php endif ?>
