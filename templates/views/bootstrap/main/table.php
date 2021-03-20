        <div class="row">
            <ul class="list-group">
                <li class="list-group-item"><?php echo $this->title ?></li>
                <li class="list-group-item"><?php echo $this->comment ?></li>
            </ul>
        </div>

        <div class="row">
            <ul class="nav nav-pills">
<?php $first = true; foreach($this->tabs as $id => $tab): ?>
                <li role="presentation" <?php
                    if($first): ?> class="active"<?php
                        $first = false; endif ?>><a data-toggle="pill" href="#tab-content-<?php
                    echo $id ?>"><?php echo $tab ?></a></li>
<?php endforeach ?>
            </ul>
            <div class="tab-content">
<?php $first = true; foreach($this->tabs as $id => $tab): ?>
                <div id="tab-content-<?php echo $id ?>" class="tab-pane fade in<?php
                    if($first): ?> active<?php $first = false; endif ?>" style="padding: 10px 20px;">
                </div>
<?php endforeach ?>
            </div>
        </div>
