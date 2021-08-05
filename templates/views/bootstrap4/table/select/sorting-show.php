        <div class="row row-no-gutters">
<?php $i = 0; foreach($this->options['values'] as $value): ?>
            <div class="col-md-6">
                <input class="form-control" name="order[<?php
                    echo $i ?>]" value="<?php echo $value['col'] ?>" />
            </div>
            <div class="col-md-6">
                <input class="form-control" name="desc[<?php
                    echo $i ?>]" value="<?php echo $value['desc'] ?>" />
            </div>
<?php $i++; endforeach ?>
        </div>
