function bootstrapJs() {
  load_dates();
  load_password();
  load_charts();
  load_intl();
  load_helpers();
  load_menuJs();
  load_autocomplete();
}

function bootstrapOnce() {
  load_drop();
}

$(document).ready(function () {
  bootstrapJs();
  bootstrapOnce();

  $(document).on('ajax:loaded', function (e, $this) {
    appJs();
    bootstrapJs();
  });
});
