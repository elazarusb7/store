Drupal.behaviors.pepIef = {
    attach: function (context, settings) {
        var removeAll = document.querySelectorAll('input[data-drupal-selector*="ief-remove-confirm"]');
        //console.log(removeAll.length);
        for (i = 0; i < removeAll.length; ++i) {
            console.log(removeAll[i]);
            removeAll[i].value = "Yes";
        }

        var cancelAll = document.querySelectorAll('input[data-drupal-selector*="ief-remove-cancel"]');
        //console.log(cancelAll.length);
        for (i = 0; i < cancelAll.length; ++i) {
            console.log(removeAll[i]);
            cancelAll[i].value = "No";
        }

        /*var remove = document.querySelectorAll('input[data-drupal-selector="edit-order-items-form-entities-0-form-actions-ief-remove-confirm"]');
        remove[0].value = 'Yes';
        var cancel = document.querySelectorAll('input[data-drupal-selector="edit-order-items-form-entities-0-form-actions-ief-remove-cancel"]');
        cancel[0].value = 'No';*/
    }
};
