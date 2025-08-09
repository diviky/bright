jQuery(document).ready(function ($) {
  brightJs();
  brightBootJs();
  brightSystemJs();
  window.pjax = brightPjax();

  $(document).on('ajax:modal:loaded', function (e, $this) {
    //brightSystemJs();
    //brightJs();
  });

  $(document).on('ajax:loaded', function (e, $this) {
    brightJs();
  });

  $(document).on('form:submit', function (e, $this) {
    var form = getForm($this);
    form.submit();
  });
});
