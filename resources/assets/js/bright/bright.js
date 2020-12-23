function brightJs() {
  jQuery('[role="validate"]').validate();
  jQuery('[role="validator"]').validator();
  jQuery('[role="krender"]').easyRender();
  jQuery('[role="ksubmit"]').easySubmit();
  jQuery('[tooltip=modal], [role="modal"]').easyModal();

  $(document).tooltip({
    html: true,
    selector: '[data-toggle="tooltip"]'
  });

  $(document).popover({
    html: true,
    sanitize: false,
    selector: '[data-toggle="popover"]'
  });

  $('[data-toggle="tooltip"]').on('remove hide', function(e) {
    $(this).tooltip('hide');
  });

  $('[data-toggle="popover"]').on('remove hide', function(e) {
    $(this).popover('hide');
  });

  if ($.fn.lazyload) {
    $('img[data-original]').lazyload({
      effect: 'fadeIn'
    });
  }

  if ($.fn.slimscroll) {
    $('[role=scroll]').slimscroll({
      height: 'auto',
      railVisible: true,
      size: '5px',
      wheelStep: 10
    });
  }

  if (typeof ClipboardJS === 'function') {
    var clipboard = new ClipboardJS('[data-clipboard]');
    clipboard.on('success', function(e) {
      e.clearSelection();
      $('.tooltip-inner').html('Copied!');
      $(e.trigger).tooltip('update');
    });
    clipboard.on('error', function(e) {
      noty({
        text: 'Error!',
        type: 'info'
      });
    });
  }

  if ($.fn.select2) {
    $('[data-select]').select2({
      minimumResultsForSearch: 10
    });

    $('[tokenizer]').select2({
      tags: true,
      tokenSeparators: [',', ' ']
    });

    $('[data-select-ajax]').each(function() {
      var $this = $(this);
      var url = $this.data('select-ajax');

      $this.select2({
        minimumInputLength: 3,
        maximumInputLength: 20,
        ajax: {
          url: url,
          delay: 250,
          processResults: function(data) {
            // Tranforms the top-level key of the response object from 'items' to 'results'
            return {
              results: data.rows
            };
          }
        }
      });
    });
  }

  // Drag and drop sortable
  if ($.fn.sortable) {
    var _gridSortHelper = function(e, ui) {
      ui.children().each(function() {
        $(this).width($(this).width());
      });
      return ui;
    };

    var _gridSortUpdateHandler = function(e, ui) {
      var form = getForm($(this));
      var page = 1;

      if (form.find('.ac-task-input').length > 0) {
        $('.ac-task-input').val('sorting');
      } else {
        $('<input/>', {
          class: 'ac-task-input',
          name: 'task',
          value: 'sorting',
          type: 'hidden'
        }).appendTo(form);
      }

      form.find("input[name='page']").val(page);
      $('#page').val(page);

      form.submit();
      return ui;
    };

    $('.table_sortable_body')
      .sortable({
        //containment: '.ac-ui-sortable',
        connectWith: ['.table_sortable_body'],
        handle: '[sortable]',
        opacity: 0.6,
        helper: _gridSortHelper,
        update: _gridSortUpdateHandler
      })
      .disableSelection();

    $('[sortables]')
      .sortable({
        connectWith: ['.table_sortable_body'],
        handle: '[sortable]',
        opacity: 0.6
      })
      .disableSelection();
  }

  if ($.fn.waypoint) {
    $('[data-waypoint]').each(function() {
      var $this = $(this);
      $this.waypoint({
        handler: function(direction) {
          $this.toggleClass('navbar-inverse', direction == 'down');
          $this.toggleClass('navbar-default', direction == 'up');
          $this.toggleClass('sticky', direction == 'down');
          $('body').toggleClass('sticky', direction == 'down');
        },
        offset: -50
      });
    });
  }
}

function brightBootJs() {
  $(document).on('click', '[data-bootstrap-select] li', function(e) {
    e.preventDefault();
    var param = $(this).data('param');
    var concept = $(this).text();
    $(this)
      .parents('div:first')
      .find('[data-concept]')
      .text(concept);
    $(this)
      .parents('div:first')
      .find('[data-param]')
      .val(param)
      .trigger('change');
  });

  $(document).on('click', '.dropdown-select .dropdown-item', function(e) {
    let $this = $(this);
    var dropdown = $this.parents('.dropdown:first');
    var target = dropdown.find('.dropdown-toggle');
    target
      .prev('input')
      .val($this.data('value'))
      .trigger('change');
    target.html($this.html());
  });

  $(window).on('scroll', function() {
    var scroll = $(window).scrollTop();
    $('[data-sticky]').each(function(index, el) {
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

  $('[data-interval]').each(function() {
    var $this = $(this);
    var val = parseInt($this.data('interval'));

    if (val > 0) {
      val = val * 1000;
      setInterval(function() {
        var form = getForm($this);
        form.submit();
      }, val);
    }
  });
}
