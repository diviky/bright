jQuery(document).ready(function ($) {
  brightJs();
  brightBootJs();
  brightSystemJs();
  brightPjax();

  $(document).on('ajax:modal:loaded', function (e, $this) {
    //brightSystemJs();
  });

  $(document).on('ajax:loaded', function (e, $this) {
    brightJs();
  });

  $(document).on('form:submit', function (e, $this) {
    var form = getForm($this);
    form.submit();
  });
});
