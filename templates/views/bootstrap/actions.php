            <!-- <div class="btn-group btn-group-justified" role="group" aria-label="...">
<?php foreach($this->actions as $name => $title): ?>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-default"><?php echo $title ?></button>
                </div>
<?php endforeach ?>
            </div> -->
            <ul class="nav nav-pills nav-justified">
<?php foreach($this->actions as $title): ?>
                <li role="presentation"><a href="#"><?php echo $title ?></a></li>
<?php endforeach ?>
            </ul>
