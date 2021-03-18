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
                            <th><?php echo $details['name'] ?></th>
                            <td><?php echo $details['engine'] ?></td>
                            <td></td>
                            <!-- <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td> -->
                            <td><?php echo $details['comment'] ?></td>
                        </tr>
<?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
