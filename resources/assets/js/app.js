function appJs() {}
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

$(document).on('ready', function() {
  appJs();
  bootstrapJs();
  bootstrapOnce();

  $(document).on('ajax:loaded', function(e, $this) {
    appJs();
    bootstrapJs();
  });
});
