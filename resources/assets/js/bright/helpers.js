window.getForm = ($this) => {
  if ($this && $this.parents('form:eq(0)').length > 0) {
    return $this.parents('form:eq(0)');
  }

  if ($this && $this.parents().find('[ajax-form]').length > 0) {
    return $('[ajax-form]:first');
  }

  let render = $('[easyrender]:first');

  return render && render.length > 0 ? render : $('[role="krender"]:first');
};

window.isSuccess = (status) => {
  if (status == 'OK' || status == 'success' || status == 200) {
    return true;
  }

  return false;
};

window.displayNoty = (response, $this) => {
  var res = parseJSON(response);

  if (res.message) {
    if (isSuccess(res.status)) {
      notify({ text: res.message, type: 'success' });
    } else if (res.status == 'INFO') {
      notify({ text: res.message, type: 'info' });
    } else {
      notify({ text: res.message, type: 'error' });
    }
  }

  if (res.login) {
    var link = 'login';
    link += res.next ? '?next=' + encodeURI(res.next) : '';

    res.link = {};
    res.link.url = link;
  }

  if (res.link && res.link.url) {
    var opts = {
      url: res.link.url,
      event: 'ready',
    };

    if (res.link.options) {
      var opts = $.extend({}, res.link.options, opts);
    }

    $.fn.easyModalShow(opts);

    return true;
  }

  if (res.modal) {
    if (!res.modal.body) {
      notify({
        text: 'Invalid response from server. Please try again.',
        type: 'error',
      });
      return true;
    }

    var opts = {
      content: res.modal.body,
      event: 'ready',
    };

    if (res.modal.options) {
      var opts = $.extend({}, res.modal.options, opts);
    }

    $.fn.easyModalShow(opts);

    return true;
  }

  if (res.redirect) {
    var url = res.redirect;
    url = url.replace('&amp;', '&');
    setTimeout(function () {
      window.top.location.href = url;
    }, 1000);
  }

  if ($this) {
    var callback = $this.data('callback');

    if (typeof callback == 'function') {
      callback(res, $this);
    } else if (typeof window[callback] == 'function') {
      window[callback](res, $this);
    }

    if ($this.data('hide')) {
      $.fn.easyModalHide();
    }

    if ($this.data('remove')) {
      $this.fadeOut();
    }

    if ($this.data('render')) {
      let val = $this.data('render');
      if (typeof val === 'string') {
        var form = $(val);
      } else {
        var form = getForm($this);
      }
      if (form) {
        form.submit();
      }
    }
  }
};

window.displayNoti = (xhr, $this) => {
  displayNoty(xhr.responseText, $this);
};

window.parseJSON = (response) => {
  if ('object' === typeof response) {
    return response;
  }

  try {
    var res = JSON.parse(response);
  } catch (err) {
    var res = response;
    if ('object' !== typeof res) {
      res = {};
    }
  }

  return res;
};

window.isJson = (response) => {
  if ('object' === typeof response) {
    return true;
  }

  try {
    JSON.parse(response);
  } catch (err) {
    return false;
  }

  return true;
};

window.notify = (message) => {
  let type = message.type == 'OK' ? 'success' : message.type;
  type = type == 'ERROR' ? 'error' : type;
  type = type == 'INFO' ? 'info' : type;

  let text = message.text || message.message;

  new Noty({
    text: text,
    type: type || 'error',
    layout: 'bottomRight',
    theme: 'nest',
    dismissQueue: false,
    timeout: 1 * 30 * 1000,
    progressBar: true,
    maxVisible: 5,
    killer: true,
  }).show();
};

window.trace = (text) => {
  if (window['console'] !== undefined) {
    console.log(text);
  }
};

window.e = (s) => {
  var argv = Array.apply(null, arguments).slice(1);
  $.each(argv, function (i) {
    s = s.replace(this, argv[i]);
  });
  return s;
};
