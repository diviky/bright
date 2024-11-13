window.load_menuJs = () => {
  $('#toggle-sidebar').on('click', function () {
    if ($('.sided-layout').hasClass('sidebar-mini')) {
      $('.sided-layout').removeClass('sidebar-mini');
      setCookie('sbm', 0, 1);
    } else {
      $('.sided-layout').addClass('sidebar-mini');
      setCookie('sbm', 1, 100);
    }
  });

  $(document).ajaxComplete(function () {
    $('[navbar]').collapse('hide');
  });
};
