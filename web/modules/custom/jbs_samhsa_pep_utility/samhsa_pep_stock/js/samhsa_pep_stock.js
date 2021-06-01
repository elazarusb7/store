Drupal.behaviors.select_pallot = {
    attach: function (context, settings) {
        jQuery('input[name="pallets"]').click(function(){
            var radioValue = jQuery("input[name='pallets']:checked").val();
            if(radioValue){
                //alert("Option Selected - " + radioValue);
                //jQuery('select[name^="source_zone"] option:selected').attr("selected",null);
                jQuery('select[name^="source_zone"] option[value=' + radioValue + ']').attr("selected","selected");
            }
        });
    }
};

