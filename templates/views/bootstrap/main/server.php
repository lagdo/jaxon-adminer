        <div class="row">
            <ul class="list-group">
<?php foreach($this->messages as $message): ?>
                <li class="list-group-item"><?php echo $message ?></li>
<?php endforeach ?>
            </ul>
        </div>

        <div class="row" style="margin-bottom:10px;">
            <div class="btn-group btn-group-justified" role="group">
<?php foreach($this->main_actions as $title): ?>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-default"><?php echo $title ?></button>
                </div>
<?php endforeach ?>
            </div>
        </div>

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
                            <td><?php echo $detail ?></td>
<?php endforeach ?>
                        </tr>
<?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
