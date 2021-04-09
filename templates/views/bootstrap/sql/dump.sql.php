<?php if(is_array($this->headers)): ?>
-- Adminer <?php echo $this->headers['version'], ' ',
                    $this->headers['driver'], ' ',
                    $this->headers['server'] ?> dump

<?php if(($this->headers['sql'])): ?>
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
<?php if(($this->headers['data_style'])): ?>
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
<?php endif ?>
<?php endif ?>
<?php endif ?>
<?php foreach($this->queries as $query): ?>
<?php echo $query, "\n" ?>
<?php endforeach ?>
