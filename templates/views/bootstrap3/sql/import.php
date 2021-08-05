<div class="col-md-12" id="adminer-command-details">
</div>
<div class="col-md-12">
    <form class="form-horizontal" role="form" id="<?php echo $this->formId ?>">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-md-4"><?php echo $this->labels['file_upload'] ?></label>
<?php if(isset($this->contents['upload'])): ?>
                    <div class="col-md-8">
                        <?php echo $this->contents['upload'] ?>
                    </div>
<?php else: ?>
                    <div class="col-md-8">
                        <?php echo $this->contents['upload_disabled'] ?>
                    </div>
<?php endif ?>
                </div>
                <div class="form-group">
<?php if(isset($this->contents['upload'])): ?>
                    <label for="sql_files" class="col-md-2">&nbsp;</label>
                    <div class="col-md-10">
                        <div class="input-group" id="<?php echo $this->sqlFilesDivId ?>">
                            <span class="input-group-btn">
                                <button class="btn btn-primary" type="button" id="<?php
                                    echo $this->sqlChooseBtnId ?>"><?php echo $this->labels['select'] ?>&hellip;</button>
                            </span>
                            <input type="file" name="sql_files[]" id="<?php
                                echo $this->sqlFilesInputId ?>" style="display: none;" multiple>
                            <input type="text" class="form-control" readonly />
                        </div>
                    </div>
<?php endif ?>
                </div>
                <div class="form-group">
                    <div class="col-md-4 col-md-offset-2">
                        <button class="btn btn-primary btn-block" type="button" id="<?php
                            echo $this->sqlFilesBtnId ?>"><?php echo $this->labels['execute'] ?></button>
                    </div>
                </div>
            </div>
<?php if(isset($this->contents['path'])): ?>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="col-md-4"><?php echo $this->labels['from_server'] ?></label>
                    <div class="col-md-8">
                        <?php echo $this->labels['path'] ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-2">&nbsp;</label>
                    <div class="col-md-10">
                    <input type="text" class="form-control" value="<?php
                        echo $this->contents['path'] ?>" readonly />
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-4 col-md-offset-2">
                        <button class="btn btn-primary btn-block" type="button" id="<?php
                            echo $this->webFileBtnId ?>" href="javascript:void(0)"><?php
                            echo $this->labels['run_file'] ?></button>
                    </div>
                </div>
            </div>
<?php endif ?>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div class="col-md-3 col-md-offset-3">
                        <div class="input-group">
                            <span class="input-group-addon"><input type="checkbox" name="error_stops" value="1" checked /></span>
                            <input type="text" class="form-control" placeholder="<?php echo $this->labels['error_stops'] ?>" readonly />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-addon"><input type="checkbox" name="only_errors" value="1" checked /></span>
                            <input type="text" class="form-control" placeholder="<?php echo $this->labels['only_errors'] ?>" readonly />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="col-md-12" id="adminer-command-history">
</div>
<div class="col-md-12" id="adminer-command-results">
</div>
