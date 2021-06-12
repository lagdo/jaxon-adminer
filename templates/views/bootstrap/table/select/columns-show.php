        <!-- <div class="row row-no-gutters">
<?php $i = 0; foreach($this->options['select'] as $key => $value): ?>
            <div class="col-md-7">
                <input class="form-control" name="columns[<?php
                    echo $i ?>][fun]" value="<?php echo $this->options['values'][$key]['fun'] ?>" />
            </div>
            <div class="col-md-5">
                <input class="form-control" name="columns[<?php
                    echo $i ?>][col]" value="<?php echo $this->options['values'][$key]['col'] ?>" />
            </div>
<?php $i++; endforeach ?>
        </div> -->
        <div class="row row-no-gutters">
<?php $i = 0; foreach($this->options['values'] as $value): ?>
            <div class="col-md-7">
                <input class="form-control" name="columns[<?php
                    echo $i ?>][fun]" value="<?php echo $value['fun'] ?>" />
            </div>
            <div class="col-md-5">
                <input class="form-control" name="columns[<?php
                    echo $i ?>][col]" value="<?php echo $value['col'] ?>" />
            </div>
<?php $i++; endforeach ?>
        </div>
