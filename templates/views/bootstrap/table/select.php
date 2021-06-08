    <form class="form-horizontal" role="form" id="<?php echo $this->formId ?>">
        <div class="form-group">
            <div class="col-md-6">
                <div class="btn-group btn-group-justified" role="group" aria-label="...">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-default" id="<?php
                            echo $this->btnColumnsId ?>"><?php echo \adminer\lang('Select') ?></button>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-default" id="<?php
                            echo $this->btnFiltersId ?>"><?php echo \adminer\lang('Filter') ?></button>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-default" id="<?php
                            echo $this->btnSortingId ?>"><?php echo \adminer\lang('Sort') ?></button>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-default" id="<?php
                            echo $this->btnEditId ?>"><?php echo \adminer\lang('Edit') ?></button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-addon"><?php echo \adminer\lang('Limit') ?></span>
                    <input type="number" name="limit" class="form-control" value="<?php
                        echo $this->options['limit']['value'] ?>" />
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" id="<?php echo $this->btnLimitId ?>">
                            <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                        </button>
                    </span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-addon"><?php echo \adminer\lang('Text length') ?></span>
                    <input type="number" name="limit" class="form-control" value="<?php
                        echo $this->options['length']['value'] ?>" />
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" id="<?php echo $this->btnLengthId ?>">
                            <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                        </button>
                    </span>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-10">
                <?php echo $this->query ?>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-primary btn-block" id="<?php
                    echo $this->btnExecId ?>"><?php echo \adminer\lang('Execute') ?></button>
            </div>
        </div>
    </form>
