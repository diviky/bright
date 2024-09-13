function brightJs() {
  jQuery('[role="validate"]').validate();
  jQuery('[role="validator"]').validator();
  jQuery('[role="krender"], [easyrender]').easyRender();
  jQuery('[role="ksubmit"], [easysubmit]').easySubmit();
  jQuery('[tooltip=modal], [role="modal"]').easyModal();

  $('[data-toggle="tooltip"]').on('remove click mouseup mouseleave mousedown ', function (e) {
    $(this).tooltip('hide');
  });

  $('[data-toggle="popover"]').on('remove hide', function (e) {
    $(this).popover('hide');
  });

  $(document).on('ajax:modal:loaded', function (e, $this) {
    $('.tooltip').tooltip('hide');
    if ($.fn.tooltip.Constructor) {
      $('[data-toggle="tooltip"]').tooltip('hide');
    }
  });

  if ($.fn.lazyload) {
    $('img[data-original]').lazyload({
      effect: 'fadeIn',
    });
  }

  if ($.fn.slimscroll) {
    $('[role=scroll]').slimscroll({
      height: 'auto',
      railVisible: true,
      size: '5px',
      wheelStep: 10,
    });
  }

  if (typeof ClipboardJS === 'function') {
    var clipboard = new ClipboardJS('[data-clipboard]');
    clipboard.on('success', function (e) {
      e.clearSelection();
      $('.tooltip-inner').html('Copied!');
      $(e.trigger).tooltip('update');
    });
    clipboard.on('error', function (e) {
      notify({
        text: 'Error!',
        type: 'info',
      });
    });
  }

  if ($.fn.waypoint) {
    $('[data-waypoint]').each(function () {
      var $this = $(this);
      $this.waypoint({
        handler: function (direction) {
          $this.toggleClass('navbar-inverse', direction == 'down');
          $this.toggleClass('navbar-default', direction == 'up');
          $this.toggleClass('sticky', direction == 'down');
          $('body').toggleClass('sticky', direction == 'down');
        },
        offset: -50,
      });
    });
  }
}

function brightBootJs() {
  $(document).on('click', '[data-bootstrap-select] li', function (e) {
    e.preventDefault();
    var param = $(this).data('param');
    var concept = $(this).text();
    $(this).parents('div:first').find('[data-concept]').text(concept);
    $(this).parents('div:first').find('[data-param]').val(param).trigger('change');
  });

  $(document).on('click', '.dropdown-select .dropdown-item', function (e) {
    let $this = $(this);
    var dropdown = $this.parents('.dropdown:first');
    var target = dropdown.find('.dropdown-toggle');
    target.prev('input').val($this.data('value')).trigger('change');
    target.html($this.html());

    var method = $this.data('method') || 'POST';
    let link = dropdown.data('link');

    if (link && link.length > 0) {
      $.ajax({
        url: link,
        method: method,
        dataType: 'json',
        headers: {
          Accept: 'application/json',
        },
        data: {
          status: $this.data('value'),
        },
        complete: function (xhr) {
          displayNoti(xhr, $this);
        },
      });
    }
    e.preventDefault();
  });

  $(window).on('scroll', function () {
    var scroll = $(window).scrollTop();
    $('[data-sticky]').each(function (index, el) {
      var height = parseInt($(this).data('sticky')) || 200;
      if (scroll < height) {
        $(this).removeClass('sticky');
        $('body').removeClass('sticky');
      } else {
        $(this).addClass('sticky');
        $('body').addClass('sticky');
      }
    });
    return false;
  });

  $('[data-interval]').each(function () {
    var $this = $(this);
    var val = parseInt($this.data('interval'));

    if (val > 0) {
      val = val * 1000;
      setInterval(function () {
        var form = getForm($this);
        form.submit();
      }, val);
    }
  });
}
