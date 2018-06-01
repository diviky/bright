; (function (jQuery, window, undefined) {

  jQuery.uaMatch = function (ua) {
    ua = ua.toLowerCase();

    var match = /(chrome)[ \/]([\w.]+)/.exec(ua) ||
      /(webkit)[ \/]([\w.]+)/.exec(ua) ||
      /(opera)(?:.*version|)[ \/]([\w.]+)/.exec(ua) ||
      /(msie) ([\w.]+)/.exec(ua) ||
      ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec(ua) ||
      [];

    return {
      browser: match[1] || "",
      version: match[2] || "0"
    };
  };

  matched = jQuery.uaMatch(navigator.userAgent);
  browser = {};

  if (matched.browser) {
    browser[matched.browser] = true;
    browser.version = matched.version;
  }

  // Chrome is Webkit, but Webkit is also Safari.
  if (browser.chrome) {
    browser.webkit = true;
  } else if (browser.webkit) {
    browser.safari = true;
  }

  jQuery.browser = browser;

  jQuery.fn.live = function (types, data, fn) {
    jQuery(document).on(types, this.selector, data, fn);
    return this;
  };

})(jQuery, window);

function parseJSON(response) {
  if ('object' === typeof response) {
    return response;
  }

  try {
    var res = JSON.parse(response);
  } catch (err) {
    var res = response;
    if ('object' !== typeof res) {
      res = {}
    }
  }

  return res;
}

function trace(text) {
  if (window["console"] !== undefined) {
    console.log(text);
  }
}

function e(s) {
  var argv = Array.apply(null, arguments).slice(1);
  if ($.isArray(argv)) {
    $.each(argv, function (i) {
      s = s.replace(this, argv[i]);
    });
  }
  return s;
}

jQuery(document).ready(function () {
  onloadEvents();
});

function onloadEvents() {
  $("[notchecked]").each(function () {
    $(this).prev('[dummy-checkbox]').remove();
    $(this).before(
      '<input type="hidden" name="' +
      $(this).attr("name") +
      '" value="' +
      $(this).attr("notchecked") +
      '"  dummy-checkbox="true"/>'
    );
  });

  $(document).on('change', '[notchecked]', function () {
    if ($(this).is(":checked")) {
      return;
    }
    $(this).prev('[dummy-checkbox]').remove();
    $(this).before('<input type="hidden" name="' + $(this).attr("name") + '" value="' + $(this).attr("notchecked") + '"  dummy-checkbox="true"/>');
  });

  $(document).on("click", "[data-prevent]", function (e) {
    e.preventDefault();
    return false;
  });

  jQuery(document).on("click", ".easySubmit :submit, .easySubmit :button",
    function () {
      $(this)
        .parents("form:first")
        .find("input")
        .removeClass("clicked");
      $(this)
        .parents("form:first")
        .find("button")
        .removeClass("clicked");
      $(this).addClass("clicked");
    }
  );

  jQuery('.easyValidate, [role="easyValidate"]').validate();
  jQuery('.easyValidator, [role="easyValidator"]').validator();
  jQuery('.easyRender, [role="easyRender"]').easyRender();
  jQuery('.easySubmit, [role="easySubmit"]').easySubmit();
  jQuery('[tooltip=modal]').easyModal();

  $("[data-drop]").on("change", "input[type=file]", function (e) {
    e.preventDefault();
    var $this = $(this)
      .parent()
      .find(".drop-preview");
    var files = $(this).prop("files");
    $(this)
      .parents("form:first")
      .submit();
    drawPreview($this, files);
  });
}

jQuery(document).ready(function ($) {

  $(document).on("click", ".noty_close", function (e) {
    $(this)
      .parents(".noty_bar:first")
      .slideUp("slow");
  });

  $(document).on("click", "div.is-invalid-feedback", function () {
    $(this).fadeOut();
  });


  $(document).on("keyup blur", "[data-slug]", function (e) {
    var target = $(this).data("slug");
    var realTarget = "[data-slug-" + target + "]";

    var value = $(this).val();
    value = $.trim(value);
    value = value
      .replace(/[^a-z0-9-]/gi, "-")
      .replace(/-+/g, "-")
      .replace(/^-|-$/g, "");
    value = value.toLowerCase();

    $(realTarget).val(value);
  });

  $(document).on("keyup blur", "[data-copy]", function (e) {
    var target = $(this).data("copy");
    var realTarget = "[data-copy-" + target + "]";

    $(realTarget).val(value);
  });

  $(document).on("click", "[data-filter]", function () {
    $(this)
      .parent("form")
      .find("input[name=page]")
      .val(1);
  });

  $(document).on("click", ".ac-add", function () {
    $(".ac-attrs tfoot:first tr")
      .clone()
      .appendTo(".ac-attrs tbody");
  });

  $(document).on("click", ".ac-remove", function () {
    $(this)
      .parents("tr:eq(0)")
      .remove();
  });

  //prevent hash url
  $(document).on("click", 'a[href="#"]', function (e) {
    e.preventDefault();
  });

  $(document).on("click", "[data-dropzone]", function () {
    $(this)
      .next("input[type=file]")
      .click();
  });

  $(document).on("dragover", "[data-drop]", function (e) {
    $(this)
      .find("[data-dropzone]")
      .addClass("drag-hover");
    e.preventDefault();
  });

  $(document).on("dragleave", "[data-drop]", function (e) {
    $(this)
      .find("[data-dropzone]")
      .removeClass("drag-hover");
    e.preventDefault();
  });

  $(document).on("drop", "[data-drop]", function (e) {
    e.preventDefault();
    var $this = $(this).find(".drop-preview");
    var files = e.originalEvent.dataTransfer.files;
    $(this)
      .parents("form:first")
      .submit();
    drawPreview($this, files);
  });

  $(document).on("click", "[data-task]", function () {
    var t = $(this)
      .parents("form:first")
      .find("input[name=task]");
    t.data("task", t.val());
    t.val($(this).data("task"));
  });

  $(document).on("click", "[role=login]", function (e) {
    if (is_user_logged_in) {
      return true;
    }

    e.preventDefault();
    var $this = $(this);
    var next = $this.data("next") || 1;
    var url = $this.attr("href") ? $this.attr("href") : "/login";

    $.fn.easyModalShow({
      url: url,
      event: 'ready'
    });
  });

  if ($.support.pjax) {
    $(document).on("click", "[data-pjax] a, a[data-pjax]", function (event) {
      var container = $(this).data("pjax-container") || "[data-pjax-container]";
      $(this)
        .parents("[data-pjax]")
        .find("a")
        .removeClass("active");
      $(this).addClass("active");
      $.pjax.click(event, { container: container });
    });
  }
});

