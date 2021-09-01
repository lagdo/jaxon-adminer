                <div class="list-group">
<?php foreach($this->menuActions as $id => $title): ?>
                    <a href="javascript:void(0)" class="adminer-menu-item list-group-item menu-action-<?php
                        echo $id ?>" id="adminer-menu-action-<?php echo $id ?>"><?php echo $title ?></a>
<?php endforeach ?>
                </div>
