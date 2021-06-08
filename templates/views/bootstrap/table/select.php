    <form class="form-horizontal" role="form" id="<?php echo $this->formId ?>">
        <div class="form-group">
            <div class="col-md-5">
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
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-addon"><?php echo \adminer\lang('Limit') ?></span>
                    <input type="number" name="limit" class="form-control" value="<?php
                        echo $this->options['limit']['value'] ?>" />
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-addon"><?php echo \adminer\lang('Text length') ?></span>
                    <input type="number" name="limit" class="form-control" value="<?php
                        echo $this->options['length']['value'] ?>" />
                </div>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-primary" id="<?php
                    echo $this->btnActionId ?>"><?php echo \adminer\lang('Apply') ?></button>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-12">
                <?php echo $this->query ?>
            </div>
        </div>
    </form>
