<?php if(isset($this->actions)): ?>
<div class="btn-group" role="group" style="padding:10px;">
<?php foreach($this->actions as $id => $label): ?>
    <button type="button" class="btn btn-default" id="adminer-main-action-<?php echo $id ?>"><?php echo $label ?></button>
<?php endforeach ?>
</div>
<?php endif ?>
