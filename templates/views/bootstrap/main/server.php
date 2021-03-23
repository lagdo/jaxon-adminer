        <div class="row">
            <ul class="list-group">
<?php foreach($this->messages as $message): ?>
                <li class="list-group-item"><?php echo $message ?></li>
<?php endforeach ?>
            </ul>
        </div>

        <div class="row" style="margin-bottom:10px;">
            <div class="btn-group btn-group-justified" role="group">
<?php foreach($this->main_actions as $id => $title): ?>
                <div class="btn-group" role="group">
                    <button id="adminer-main-action-<?php
                        echo $id ?>" type="button" class="btn btn-default"><?php echo $title ?></button>
                </div>
<?php endforeach ?>
            </div>
        </div>

        <div id="adminer-server-main-table" class="row">
        </div>
