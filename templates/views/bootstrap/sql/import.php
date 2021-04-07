            <form class="form-horizontal" id="form">
                <div class="row">
                    <div class="col-md-7">
                        <div class="form-group">
                            <label for="output" class="col-md-9"><?php echo $this->labels['file_upload'] ?></label>
                        </div>
                        <div class="form-group">
<?php if(isset($this->contents['upload'])): ?>
                            <div class="col-md-4">
                                <?php echo $this->contents['upload'] ?>
                            </div>
                            <div class="col-md-8">
                                <div class="input-group" id="adminer-import-sql-file-upload">
                                    <label class="input-group-btn">
                                        <span class="btn btn-primary" style="margin:0">
                                            <?php echo $this->labels['select'] ?>&hellip;
                                            <input type="file" name="sql_file[]" style="display: none;" multiple>
                                        </span>
                                    </label>
                                    <input type="text" class="form-control" readonly>
                                </div>
                            </div>
<?php else: ?>
                            <div class="col-md-12">
                                <?php echo $this->contents['upload_disabled'] ?>
                            </div>
<?php endif ?>
                        </div>
                    </div>
<?php if(isset($this->contents['path'])): ?>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="output" class="col-md-9"><?php echo $this->labels['from_server'] ?></label>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <?php echo $this->contents['path'] ?>
                            </div>
                        </div>
                    </div>
<?php endif ?>
                </div>
                <div class="row">
                    <div class="col-md-7">
                        <div class="form-group">
                            <div class="col-md-4">
                                <button class="btn btn-primary btn-block" type="button" href="javascript:void(0)"><?php
                                    echo $this->labels['execute'] ?></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <div class="col-md-4">
                                <button class="btn btn-primary btn-block" type="button" href="javascript:void(0)"><?php
                                    echo $this->labels['run_file'] ?></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label><input type='checkbox' name='error_stops' value='1' checked /><?php
                                        echo $this->labels['error_stops'] ?></label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label><input type='checkbox' name='only_errors' value='1' checked /><?php
                                        echo $this->labels['only_errors'] ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
