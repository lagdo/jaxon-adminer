<?php if(isset($this->mainActions)): ?>
<div class="btn-group" role="group" style="padding:10px;">
<?php foreach($this->mainActions as $id => $label): ?>
    <button type="button" class="btn btn-default" id="adminer-main-action-<?php echo $id ?>"><?php echo $label ?></button>
<?php endforeach ?>
</div>
<?php endif ?>
