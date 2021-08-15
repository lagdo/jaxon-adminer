<table class="table table-bordered">
    <thead>
        <tr>
<?php foreach($this->headers as $header): ?>
            <th><?php if(is_array($header)) echo $header['key'] ?></th>
<?php endforeach ?>
        </tr>
    </thead>
    <tbody>
<?php $rowId = 0; foreach($this->rows as $row): ?>
        <tr>
            <th>
                <div class="btn-group" role="group" data-row-id="<?php echo $rowId++ ?>">
                    <button type="button" class="btn btn-outline-primary btn-sm <?php
                        echo $this->btnEditRowClass ?>"><i class="bi bi-pencil-square"></i></button>
                    <button type="button" class="btn btn-outline-danger btn-sm <?php
                        echo $this->btnDeleteRowClass ?>"><i class="bi bi-x-square"></i></button>
                </div>
            </th>
<?php foreach($row['cols'] as $col): ?>
            <td><?php echo $col['val'] ?></td>
<?php endforeach ?>
        </tr>
<?php endforeach ?>
    </tbody>
</table>
