<div class="portlet-body form">
    <form id="<?php echo $this->formId ?>">
        <div class="form-group row">
            <div class="col-md-1 col-md-offset-9">
                <button type="button" class="btn btn-primary" id="<?php
                    echo $this->formId ?>-add" onclick="<?php echo $this->btnAdd ?>">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger" id="<?php
                    echo $this->formId ?>-del" onclick="<?php echo $this->btnDel ?>">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>
<?php $i = 0; foreach($this->options['values'] as $value): ?>
        <div class="form-group row" id="<?php echo $this->formId ?>-item-<?php echo $i ?>">
            <div class="col-md-6">
                <select name="order[<?php echo $i ?>]" class="form-control">
<?php foreach($this->options['columns'] as $column): ?>
                    <option <?php if($value['col'] == $column): ?>selected<?php
                        endif ?>><?php echo $column ?></option>
<?php endforeach ?>
                </select>
            </div>
            <div class="col-md-5">
                <div class="input-group mb-3">
                    <span class="input-group-prepend">
                        <input type="checkbox" name="desc[<?php echo $i ?>]"<?php
                            if($value['desc']): ?> checked<?php endif ?> value="1" />
                    </span>
                    <label for="desc[<?php echo $i ?>]" class="form-control"><?php
                        echo $this->trans->lang('descending') ?></label>
                </div>
            </div>
            <div class="col-md-1">
                <input type="checkbox" data-index="<?php echo $i ?>" class="<?php
                    echo $this->formId ?>-item-checkbox" />
            </div>
        </div>
<?php $i++; endforeach ?>
    </form>
    <!-- Empty line for new entry (must be outside the form) -->
    <div id="<?php echo $this->formId ?>-item-template" style="display:none">
        <div class="form-group row" id="<?php echo $this->formId ?>-item-__index__">
            <div class="col-md-6">
                <select name="order[__index__]" class="form-control">
<?php foreach($this->options['columns'] as $column): ?>
                    <option><?php echo $column ?></option>
<?php endforeach ?>
                </select>
            </div>
            <div class="col-md-5">
                <div class="input-group mb-3">
                    <span class="input-group-prepend">
                        <input name="desc[__index__]" type="checkbox" value="1" />
                    </span>
                    <label for="desc[__index__]" class="form-control"><?php
                        echo $this->trans->lang('descending') ?></label>
                </div>
            </div>
            <div class="col-md-1">
                <input type="checkbox" data-index="__index__" class="<?php
                    echo $this->formId ?>-item-checkbox" />
            </div>
        </div>
    </div>
</div>
