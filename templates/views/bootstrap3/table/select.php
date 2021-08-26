<div class="row">
    <div class="col-md-12">
        <form class="form-horizontal" role="form" id="<?php echo $this->formId ?>">
            <div class="form-group">
                <div class="col-md-6">
                    <div class="btn-group btn-group-justified" role="group">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-default" id="<?php
                                echo $this->btnColumnsId ?>"><?php echo $this->trans->lang('Columns') ?></button>
                        </div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-default" id="<?php
                                echo $this->btnFiltersId ?>"><?php echo $this->trans->lang('Filters') ?></button>
                        </div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-default" id="<?php
                                echo $this->btnSortingId ?>"><?php echo $this->trans->lang('Order') ?></button>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-addon"><?php echo $this->trans->lang('Limit') ?></span>
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
                        <span class="input-group-addon"><?php echo $this->trans->lang('Text length') ?></span>
                        <input type="number" name="text_length" class="form-control" value="<?php
                            echo $this->options['length']['value'] ?>" />
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button" id="<?php echo $this->btnLengthId ?>">
                                <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
            <div class="form-group" style="display:none">
                <div class="col-md-4" id="adminer-table-select-columns-show">
<?php echo $this->render('adminer::views::table/select/columns-show', [
    'trans' => $this->trans,
    'options' => $this->options['columns'],
]) ?>
                </div>
                <div class="col-md-4" id="adminer-table-select-filters-show">
<?php echo $this->render('adminer::views::table/select/filters-show', [
    'trans' => $this->trans,
    'options' => $this->options['filters'],
]) ?>
                </div>
                <div class="col-md-4" id="adminer-table-select-sorting-show">
<?php echo $this->render('adminer::views::table/select/sorting-show', [
    'trans' => $this->trans,
    'options' => $this->options['sorting'],
]) ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-9">
                    <pre id="<?php echo $this->txtQueryId ?>"><?php echo $this->query ?></pre>
                </div>
                <div class="col-md-3">
                    <div class="btn-group btn-group-justified" role="group">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-default" id="<?php
                                echo $this->btnEditId ?>"><?php echo $this->trans->lang('Edit') ?></button>
                        </div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-primary" id="<?php
                                echo $this->btnExecId ?>"><?php echo $this->trans->lang('Execute') ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="row">
    <div class="col-md-12" id="adminer-table-select-pagination">
    </div>
    <div class="col-md-12" id="adminer-table-select-results">
    </div>
</div>
