<div class="row" style="margin-top: 20px;">
    <div class="col-md-3">
        <div class="row" style="padding:0px 10px 5px 10px;">
            <div class="input-group">
                <select class="form-control" id="adminer-dbhost-select">
<?php foreach($this->servers as $name => $title): ?>
                    <option value="<?php echo $name ?>"<?php if($name == $this->default): ?> selected="selected"<?php endif?>><?php echo $title ?></option>
<?php endforeach ?>
                </select>
                <span class="input-group-btn">
                    <button class="btn btn-primary" type="button" onclick="<?php echo $this->connect ?>; return false">Connect</button>
                </span>
            </div>
        </div>
        <div class="row" id="<?php echo $this->serverActionsId ?>" style="padding:0px 10px;">
        </div>
        <div class="row" id="<?php echo $this->dbListId ?>" style="padding:15px 10px 5px 10px;">
        </div>
        <div class="row" id="<?php echo $this->dbActionsId ?>" style="padding:0px 10px;">
        </div>
        <div class="row" id="<?php echo $this->dbMenuId ?>" style="padding:15px 10px 5px 10px;">
        </div>
    </div>
    <div class="col-md-9" id="<?php echo $this->dbContentId ?>">
    </div>
</div>
