window.nl2br = (str, is_xhtml) => {
  if (typeof str === 'undefined' || str === null) {
    return '';
  }
  var breakTag = is_xhtml || typeof is_xhtml === 'undefined' ? '<br />' : '<br>';
  return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
};

// load JSON files from url
window.loadJSON = (url, callback, headers) => {
  $.ajax({
    url: url,
    method: 'GET',
    dataType: 'json',
    headers: headers,
    success: function (res) {
      callback(res);
    },
    fail: function () {
      callback(null, 'error');
    },
    error: function () {
      callback(null, 'error');
    },
  });
};

window.uuid = () => {
  function s4() {
    return Math.floor((1 + Math.random()) * 0x10000)
      .toString(16)
      .substring(1);
  }
  return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
};

const loadDynamicScript = (url, id, callback) => {
  const existingScript = document.getElementById('scriptId');

  if (!existingScript) {
    const script = document.createElement('script');
    script.src = url; // URL for the third-party library being loaded.
    script.id = id; // e.g., googleMaps or stripe
    document.body.appendChild(script);

    script.onload = () => {
      if (callback) callback();
    };
  }

  if (existingScript && callback) callback();
};

window.guid = () => {
  function s4() {
    return Math.floor((1 + Math.random()) * 0x10000)
      .toString(16)
      .substring(1);
  }
  return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
};

jQuery.fn.preventDoubleClick = function () {
  $(this).on('click', function (e) {
    var $el = $(this);
    if ($el.data('clicked')) {
      // Previously clicked, stop actions
      e.preventDefault();
      e.stopPropagation();
    } else {
      // Mark to ignore next click
      $el.data('clicked', true);
      // Unmark after 1 second
      window.setTimeout(function () {
        $el.removeData('clicked');
      }, 1000);
    }
  });
  return this;
};

RegExp.escape = function (s) {
  return s.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
};

window.setLivewire = function ($wire) {
  setTimeout(function () {
    $(document).trigger('ajax:loaded');
  }, 100);

  // Dispatch custom event with $wire argument
  window.dispatchEvent(
    new CustomEvent('livewire:component:rendered', {
      detail: { $wire: $wire },
    })
  );

  if (typeof $wire === 'undefined') {
    return null;
  }

  window.wire = $wire;
};

window.wired = () => {
  if (typeof window.wire === 'undefined') {
    return null;
  }

  return window.wire;
};
