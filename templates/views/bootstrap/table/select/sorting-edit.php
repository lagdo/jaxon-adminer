<div class="portlet-body form">
    <form class="form-horizontal" role="form" id="<?php echo $this->formId ?>">
<?php $i = 0; foreach($this->options['values'] as $value): ?>
        <div class="form-group">
            <div class="col-md-6">
                <select name="order[<?php echo $i ?>]" class="form-control">
<?php foreach($this->options['columns'] as $column): ?>
                    <option <?php if($value['col'] == $column): ?>selected<?php
                        endif ?>><?php echo $column ?></option>
<?php endforeach ?>
                </select>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-addon"><input type="checkbox" name="desc[<?php
                        echo $i ?>]" <?php if($value['desc']): ?>checked<?php endif ?> value="1" /></span>
                    <label for="desc[<?php echo $i ?>]" class="form-control"><?php
                        echo \adminer\lang('descending') ?></label>
                </div>
            </div>
        </div>
<?php $i++; endforeach ?>
        <!-- Empty line for new entry -->
        <div class="form-group">
            <div class="col-md-6">
                <select name="order[<?php echo $i ?>]" class="form-control">
<?php foreach($this->options['columns'] as $column): ?>
                    <option><?php echo $column ?></option>
<?php endforeach ?>
                </select>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-addon"><input name="desc[<?php
                        echo $i ?>]" type="checkbox" value="1" /></span>
                    <label for="desc[<?php echo $i ?>]" class="form-control"><?php
                        echo \adminer\lang('descending') ?></label>
                </div>
            </div>
        </div>
    </form>
</div>
