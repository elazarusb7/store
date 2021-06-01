(function ($, Drupal) {
  Drupal.behaviors.queryFilterBehavior = {
    attach: function (context, settings) {
      $("#search-query").keyup(function () {
        var searchedQuery = this.value;
        $("#te-elevated-query-collection tbody").find('tr').each(function (index, element) {
          let td = $(this).find('td');
          let queryString = td.eq(0).text();
          let n = queryString.toLowerCase().search(searchedQuery);
          if (n >= 0) {
            $(this).show();
          } else {
            $(this).hide();
          }
        });
      });
    }
  };
})(jQuery, Drupal);
