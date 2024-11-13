window.brightJs = () => {
  jQuery('[role="validate"]').validate();
  jQuery('[role="validator"]').validator();
  jQuery('[role="krender"], [easyrender]').easyRender();
  jQuery('[role="ksubmit"], [easysubmit]').easySubmit();
  jQuery('[tooltip=modal], [role="modal"]').easyModal();
  jQuery('[tooltip=popover]').easyModal();

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
    var clipboard = new ClipboardJS('[data-clipboard]', {
      text: function (trigger) {
        return trigger.getAttribute('data-clipboard');
      },
    });

    clipboard.on('success', function (e) {
      e.clearSelection();
      $('.tooltip-inner').html('Copied!');
      $(e.trigger).tooltip('update');
    });

    clipboard.on('error', function (e) {
      notify({
        text: 'Error!',
        type: 'error',
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
};
