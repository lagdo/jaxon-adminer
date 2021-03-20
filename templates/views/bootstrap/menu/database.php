            <div class="list-group">
<?php foreach($this->menu_actions as $id => $title): ?>
                <a href="javascript:void(0)" class="list-group-item" onclick="<?php echo $this->menu_handlers[$id] ?>"><?php echo $title ?></a>
<?php endforeach ?>
            </dic>
