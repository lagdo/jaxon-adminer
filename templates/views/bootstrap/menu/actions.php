<?php foreach(array_chunk($this->actions, 2) as $titles): ?>
            <div class="btn-group btn-group-justified" role="group">
<?php foreach($titles as $title): ?>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-default btn-sm"><?php echo $title ?></button>
                </div>
<?php endforeach ?>
            </div>
<?php endforeach ?>
