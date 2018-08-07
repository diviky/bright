
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

function notify(message) {
  new Noty({
    text: message.text,
    type: message.type || 'error',
    layout: 'topRight',
    theme: 'nest',
    timeout: (2 * 60 * 1000)
  }).show();
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
    $(this).before('<input type="hidden" name="' + $(this).attr("name") + '" value="' + $(this).attr("notchecked") + '"  dummy-checkbox="true"/>');
  });

  $(document).on('change', '[notchecked]', function () {
    $(this).prev('[dummy-checkbox]').remove();
    $(this).before('<input type="hidden" name="' + $(this).attr("name") + '" value="' + $(this).attr("notchecked") + '"  dummy-checkbox="true"/>');
  });

  $(document).on("click", "[data-prevent]", function (e) {
    e.preventDefault();
    return false;
  });
}

jQuery(document).ready(function ($) {

  $(document).on('ajax:loaded', function (e, $this) {
    onloadEvents();
  });

  $.ajaxSetup({
    headers: {
      "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
    }
  });

  $(document).on("click", ".noty_close", function (e) {
    $(this)
      .parents(".noty_bar:first")
      .slideUp("slow");
  });

  $(document).on("click", "div.is-invalid-feedback", function () {
    $(this).fadeOut();
  });


  $(document).on('form:reset', function (e) {
    var form = getForm($(this));
    form.find("input[name=page]").val(1);
    $('#page').val(1);

    var total = form.find('[data-total]').data('total');

    if (total == '' || total == undefined) {
      var total = form.find('input[name=total]').val();
    }

    form.find("input[name=total]").val(total);
    form.find('[ajax-total]').html(total);

  });

  $(document).on("click", "[data-filter]", function () {
    $(document).trigger('form:reset', $(this));
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


  $(document).on("click", "[data-add]", function (e) {
    var target = $(this).parents('table:first');
    var clone = target.find("tfoot:first tr").clone();
    target.find('tbody:first').append(clone);

    e.preventDefault();
  });

  $(document).on("click", "[data-remove]", function (e) {
    $(this)
      .parents("tr:eq(0)")
      .remove();

    e.preventDefault();
  });

  //prevent hash url
  $(document).on("click", 'a[href="#"]', function (e) {
    e.preventDefault();
  });

  $("[data-drop]").on("change", "input[type=file]", function (e) {
    e.preventDefault();
    var $this = $(this).parent().find(".drop-preview");
    var files = $(this).prop("files");
    drawPreview($this, files);
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
      $(this).parents("[data-pjax]").find("a").removeClass("active");
      $(this).addClass("active");

      $.pjax.click(event, { container: container });
    });

    $(document).on('pjax:end', function () {
      $(document).trigger('ajax:loaded');
    })
  }
});

