Drupal.behaviors.add_internal_order = {
    attach: function (context, settings) {
        radiobtn = document.getElementById("edit-type-value-default");
        radiobtn.checked = true;
        document.getElementById("edit-type-value--wrapper").style.display = "none";
    }
};

