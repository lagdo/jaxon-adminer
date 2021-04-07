jaxon.adminer = {
    countTableCheckboxes: function(checkboxId) {
        $('#adminer-table-' + checkboxId + '-count').html($('.adminer-table-' + checkboxId + ':checked').length);
    },
    selectTableCheckboxes: function(checkboxId) {
        $('#adminer-table-' + checkboxId + '-all').change(function() {
            $('.adminer-table-' + checkboxId, '#<?php
                echo $this->containerId ?>').prop('checked', this.checked);
            jaxon.adminer.countTableCheckboxes(checkboxId);
        });
        $('.adminer-table-' + checkboxId).change(function() {
            jaxon.adminer.countTableCheckboxes(checkboxId);
        });
    },
    selectAllCheckboxes: function(checkboxId) {
        $('#' + checkboxId + '-all').change(function() {
            $('.' + checkboxId, '#<?php
                echo $this->containerId ?>').prop('checked', this.checked);
        });
    },
    setFileUpload: function(container) {
        $(container).on('change', ':file', function() {
            let fileInput = $(this),
                numFiles = fileInput.get(0).files ? fileInput.get(0).files.length : 1,
                label = fileInput.val().replace(/\\/g, '/').replace(/.*\//, ''),
                textInput = $(container).find(':text'),
                text = numFiles > 1 ? numFiles + ' files selected' : label;

            if (textInput.length > 0) {
                textInput.val(text);
            }
        });
    }
}
