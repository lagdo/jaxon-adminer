<?php if(count($this->main_actions) > 0): ?>
        <div class="row" style="margin-bottom:10px;">
            <div class="btn-group btn-group-justified" role="group">
<?php foreach($this->main_actions as $title): ?>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-default"><?php echo $title ?></button>
                </div>
<?php endforeach ?>
            </div>
        </div>
<?php endif ?>

        <div class="row">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
<?php foreach($this->headers as $header): ?>
                            <th><?php echo $header ?></th>
<?php endforeach ?>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach($this->details as $details): ?>
                        <tr>
<?php foreach($details as $detail): ?>
<?php if(is_array($detail)): ?>
                            <td class="<?php
                                echo $detail['class'] ?>" data-value="<?php
                                echo $detail['value'] ?>"><a href="javascript:void(0)"><?php
                                echo $detail['label'] ?></a></td>
<?php else: ?>
                            <td><?php echo $detail ?></td>
<?php endif ?>
<?php endforeach ?>
                        </tr>
<?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
