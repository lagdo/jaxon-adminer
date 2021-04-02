<select class="form-control">
<?php foreach($this->options as $option): ?>
    <option class="<?php echo $this->optionClass ?>" value="<?php
        echo htmlentities($option) ?>"><?php echo $option ?></option>
<?php endforeach ?>
</select>
