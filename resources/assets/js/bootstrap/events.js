window.addEventListener('modal.open', function (e) {
  var opts = {
    event: 'ready',
  };

  opts = { ...opts, ...e.detail };

  $.fn.easyModalShow(opts);
});

window.addEventListener('turbo', function (e) {
  var opts = {
    container: '[data-pjax-container]',
  };
  opts = { ...opts, ...e.detail };
  $.pjax(opts);
});

window.addEventListener('flash', function (e) {
  notify(e.detail);
});
