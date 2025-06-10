window.addEventListener('modal.open', function (e) {
  var opts = {
    event: 'ready',
  };

  const detail = Array.isArray(e.detail) ? e.detail[0] : e.detail;
  opts = { ...opts, ...detail };

  $.fn.easyModalShow(opts);
});

window.addEventListener('turbo', function (e) {
  var opts = {
    container: '[data-pjax-container]',
  };
  const detail = Array.isArray(e.detail) ? e.detail[0] : e.detail;
  opts = { ...opts, ...detail };
  $.pjax(opts);
});

window.addEventListener('flash', function (e) {
  const detail = Array.isArray(e.detail) ? e.detail[0] : e.detail;
  notify(detail);
});

window.addEventListener('notify', function (e) {
  const detail = Array.isArray(e.detail) ? e.detail[0] : e.detail;
  notify(detail);
});