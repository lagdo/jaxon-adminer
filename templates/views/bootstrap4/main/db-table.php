            <!-- <div class="row">
                <div class="col-md-12">
                    <ul class="list-group">
                        <li class="list-group-item"><?php echo $this->title ?></li>
                        <li class="list-group-item"><?php echo $this->comment ?></li>
                    </ul>
                </div>
            </div> -->

            <div class="row">
                <div class="col-md-12">
                    <ul class="nav nav-pills">
<?php $first = true; foreach($this->tabs as $id => $tab): ?>
                        <li class="nav-item" role="presentation"><a class="nav-link<?php
                            if($first): ?> active<?php $first = false; endif ?>" data-toggle="tab" role="tab" href="#tab-content-<?php
                            echo $id ?>"><?php echo $tab ?></a></li>
<?php endforeach ?>
                    </ul>
                    <div class="tab-content">
<?php $first = true; foreach($this->tabs as $id => $tab): ?>
                        <div id="tab-content-<?php echo $id ?>" class="tab-pane fade<?php
                            if($first): ?> show active<?php $first = false; endif ?>" style="margin-top:10px;">
                        </div>
<?php endforeach ?>
                    </div>
                </div>
            </div>
