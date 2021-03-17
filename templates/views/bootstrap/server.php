        <div class="row">
            <ul class="list-group">
<?php foreach($this->messages as $message): ?>
                <li class="list-group-item"><?php echo $message ?></li>
<?php endforeach ?>
            </ul>
        </div>
        <div class="row" style="margin-bottom:10px;">
            <div class="btn-group btn-group-justified" role="group" aria-label="...">
<?php foreach($this->actions as $name => $action): ?>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-default"><?php echo $action ?></button>
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
                            <th><?php echo $details['name'] ?></th>
                            <td><?php echo $details['collation'] ?></td>
                            <td><?php echo $details['tables'] ?></td>
                            <td><?php echo $details['size'] ?></td>
                        </tr>
<?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
