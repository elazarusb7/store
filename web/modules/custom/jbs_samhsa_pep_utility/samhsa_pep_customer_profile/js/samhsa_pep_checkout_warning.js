Drupal.behaviors.checkout_warning = {
    attach: function (context, settings) {
        var submit_clicked = false;

        jQuery('input[id="edit-actions-next"]').click(function(){
            submit_clicked = true;
        });

        window.addEventListener("beforeunload", function (event) {
            if(submit_clicked === false) {
                // Most browsers.
                event.preventDefault();
                // Chrome/Chromium based browsers still need this one.
                event.returnValue = "\o/";
            }
        });
    }
};
