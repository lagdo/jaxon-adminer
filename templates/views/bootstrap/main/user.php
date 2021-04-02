            <div class="portlet-body form">
                <form class="form-horizontal" role="form" id="<?php echo $this->formId ?>">
                    <div class="module-body">
                        <div class="form-group">
                            <label for="host" class="col-md-3 control-label"><?php
                                echo $this->user['host']['label'] ?></label>
                            <div class="col-md-6">
                                <input type="text" name="host" class="form-control" value="<?php
                                    echo $this->user['host']['value'] ?>" data-maxlength="60" autocapitalize="off" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="user" class="col-md-3 control-label"><?php
                                echo $this->user['name']['label'] ?></label>
                            <div class="col-md-6">
                                <input type="text" name="user" class="form-control" value="<?php
                                    echo $this->user['name']['value'] ?>" data-maxlength="80" autocapitalize="off" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="pass" class="col-md-3 control-label"><?php
                                echo $this->user['pass']['label'] ?></label>
                            <div class="col-md-6">
                                <input type="text" name="pass" class="form-control" value="<?php
                                    echo $this->user['pass']['value'] ?>" autocomplete="new-password" />
                            </div>
                            <div class="col-md-2 checkbox">
                                <label for="hashed">
                                    <input type="checkbox" name="hashed"<?php
                                        if($this->user['hashed']['value']): ?> checked="checked"<?php
                                        endif ?>><?php echo $this->user['hashed']['label'] ?>
                                </label>
                            </div>
                        </div>
                    </div>

<?php echo $this->content ?>
                </form>
            </div>
