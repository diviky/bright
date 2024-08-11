function load_sortable() {
  if (typeof Sortable === 'undefined') {
    console.info('npm install sortablejs --save');
    return;
  }

  $('[grid-draggable]').each(function () {
    var $this = $(this);
    Sortable.create($this[0], {
      animation: 100,
      group: 'group',
      draggable: '[draggable]',
      handle: '[handle]',
      sort: true,
      filter: '.sortable-disabled',
      chosenClass: 'active',
    });
  });

  $('[grid-dropable]').each(function () {
    var $this = $(this);
    Sortable.create($this[0], {
      animation: 100,
      group: 'group',
      draggable: '[draggable]',
      handle: '[handle]',
      sort: true,
      filter: '.sortable-disabled',
      chosenClass: 'active',
    });
  });

  var _sortOnEndHandler = function (e, $this) {
    var form = getForm($this);

    var page = 1;
    var task = $this.attr('data-task') || 'sorting';

    if (form.find('.ac-task-input').length > 0) {
      $('.ac-task-input').val(task);
    } else {
      $('<input/>', {
        class: 'ac-task-input',
        name: 'task',
        value: task,
        type: 'hidden',
      }).appendTo(form);
    }

    form.find("input[name='page']").val(page);
    $('#page').val(page);

    form.submit();
  };

  $('[grid-sortable]').each(function () {
    var $this = $(this);
    Sortable.create($this[0], {
      animation: 100,
      //group: 'group',
      draggable: '[draggable]',
      handle: '[handle]',
      sort: true,
      filter: '.sortable-disabled',
      chosenClass: 'active',
      onEnd: function (e) {
        _sortOnEndHandler(e, $this);
      },
    });
  });
}
