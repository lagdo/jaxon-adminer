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
            <th data-row-id="<?php echo $rowId++ ?>">
                <button type="button" class="btn btn-primary btn-xs <?php echo $this->btnEditRowClass ?>">
                    <span class="glyphicon glyphicon-edit"></span>
                </button>
                <button type="button" class="btn btn-danger btn-xs <?php echo $this->btnDeleteRowClass ?>">
                    <span class="glyphicon glyphicon-remove"></span>
                </button>
            </th>
<?php foreach($row['cols'] as $col): ?>
            <td><?php echo $col['val'] ?></td>
<?php endforeach ?>
        </tr>
<?php endforeach ?>
    </tbody>
</table>
