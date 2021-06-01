(function ($, Drupal) {
  Drupal.behaviors.checkboxesManagementBehavior = {
    attach: function (context, settings) {

      $.each(settings.teRadiosManagement.checkboxesMap, function (index, value) {
        $("input[name='" + value + "']").prop('checked', true);
      });

      $('.te-radios-grid-table input[type=checkbox]').change(function () {
        let clickedId = $(this).attr('id');
        let verticalGroup = $(this).attr('vertical_group');
        $('.te-radios-grid-table input[vertical_group=' + verticalGroup + ']').each(function () {
          let elementId = $(this).attr('id');
          if (elementId !== clickedId) {
            $(this).prop('checked', false);
          }
        });
        let horizontalGroup = $(this).attr('horizontal_group');
        $('.te-radios-grid-table input[horizontal_group=' + horizontalGroup + ']').each(function () {
          let elementId = $(this).attr('id');
          if (elementId !== clickedId) {
            $(this).prop('checked', false);
          }
        });
      });

    }
  };
})(jQuery, Drupal);
