        <div class="row row-no-gutters">
<?php $i = 0; foreach($this->options['values'] as $value): ?>
            <div class="col-md-4">
                <input class="form-control" name="where[<?php
                    echo $i ?>][col]" value="<?php echo $value['col'] ?>" />
            </div>
            <div class="col-md-2">
                <input class="form-control" name="where[<?php
                    echo $i ?>][op]" value="<?php echo $value['op'] ?>" />
            </div>
            <div class="col-md-6">
                <input class="form-control" name="where[<?php
                    echo $i ?>][val]" value="<?php echo $value['val'] ?>" />
            </div>
<?php $i++; endforeach ?>
        </div>
