<div class="row" id="<?php echo $this->containerId ?>">
    <div class="col-md-3">
        <div class="row">
            <div class="col-md-12">
                <div class="input-group">
                    <select class="form-control" id="adminer-dbhost-select">
<?php foreach($this->servers as $name => $title): ?>
                        <option value="<?php echo $name ?>"<?php
                            if($name == $this->default): ?> selected="selected"<?php
                            endif?>><?php echo $title ?></option>
<?php endforeach ?>
                    </select>
                    <div class="input-group-btn">
                        <button class="btn btn-primary btn-select" type="button" onclick="<?php
                            echo $this->connect ?>; return false">Show</button>
                    </div>
                </div>
            </div>
            <div class="col-md-12" id="<?php echo $this->serverActionsId ?>">
            </div>
            <div class="col-md-12" id="<?php echo $this->dbListId ?>">
            </div>
            <div class="col-md-12" id="<?php echo $this->schemaListId ?>">
            </div>
            <div class="col-md-12" id="<?php echo $this->dbActionsId ?>">
            </div>
            <div class="col-md-12" id="<?php echo $this->dbMenuId ?>">
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="row">
            <div class="col-md-8" id="<?php echo $this->serverInfoId ?>">
            </div>
            <div class="col-md-4" id="<?php echo $this->userInfoId ?>">
            </div>
            <div class="col-md-12">
                <span id="<?php echo $this->breadcrumbsId ?>">
                </span>
                <span id="<?php echo $this->mainActionsId ?>">
                </span>
            </div>
        </row>
        <div class="col-md-12" id="<?php echo $this->dbContentId ?>">
        </div>
    </div>
</div>
