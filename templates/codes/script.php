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
    },
    onColumnRenamed: function() {
        let column = $(this).parent();
        // The get() method returns the wrapped js object.
        while ((column) && !column.get().hasAttribute('data-index')) {
            column = column.parent();
        }
        if (!column) {
            return;
        }
        const index = parseInt(column.attr('data-index'), 10) + 1;
        $(this).attr('name', 'fields[' + index + '][' + $(this).attr('data-field') + ']');
    },
    insertSelectQueryItem: function(targetId, templateId) {
        const index = jaxon.adminer.newItemIndex++;
        const itemHtml = $('#' + templateId).html().replace(/__index__/g, index);
        const targetElt = jaxon.$(targetId);
        targetElt.insertAdjacentHTML('beforeend', itemHtml);
    },
    removeSelectQueryItems: function(containerId, checkboxClass) {
        $('.' + checkboxClass + ':checked', '#' + containerId).each(function() {
            const targetId = '#' + containerId + '-item-' + $(this).attr('data-index');
            $(targetId).remove();
        });
    },
}
