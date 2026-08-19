window.load_sortable = () => {
  if (typeof Sortable === 'undefined') {
    console.info('npm install sortablejs --save');
    return;
  }

  var nestedSortOptions = function ($this) {
    var nested = $this.is('[data-nested-root]') || $this.closest('[data-nested-root]').length > 0;

    return {
      animation: 100,
      group: $this.attr('data-group') || 'group',
      draggable: '[draggable]',
      handle: '[handle]',
      sort: true,
      filter: '.sortable-disabled',
      chosenClass: 'active',
      fallbackOnBody: nested,
      swapThreshold: nested ? 0.65 : 1,
      emptyInsertThreshold: nested ? 12 : 5,
    };
  };

  var serializeNestedSortItems = function ($root) {
    var items = [];

    var walk = function ($list, parentId) {
      $list.children('[draggable]').each(function (index) {
        var $item = $(this);
        var id = $item.data('id');

        items.push({
          id: id,
          parent_id: parentId || '',
          ordering: index + 1,
        });

        var $childList = $item.children('[grid-dropable], [grid-draggable], [grid-sortable]').first();

        if ($childList.length) {
          walk($childList, id);
        }
      });
    };

    walk($root, $root.data('parent-id') || '');

    return items;
  };

  var fillNestedSortInputs = function ($root, form) {
    var items = serializeNestedSortItems($root);

    form.find('.nested-sort-input').remove();

    items.forEach(function (item, index) {
      $('<input/>', {
        type: 'hidden',
        class: 'nested-sort-input',
        name: 'items[' + index + '][id]',
        value: item.id,
      }).appendTo(form);

      $('<input/>', {
        type: 'hidden',
        class: 'nested-sort-input',
        name: 'items[' + index + '][parent_id]',
        value: item.parent_id,
      }).appendTo(form);

      $('<input/>', {
        type: 'hidden',
        class: 'nested-sort-input',
        name: 'items[' + index + '][ordering]',
        value: item.ordering,
      }).appendTo(form);
    });
  };

  var sortOnEndHandler = function (e, $this) {
    var form = getForm($this);
    var $root = $this.closest('[data-nested-root]');

    if ($root.length && form && form.length) {
      fillNestedSortInputs($root, form);
    }

    var page = 1;
    var task = $this.attr('data-task') || 'sorting';

    if (form.find('.ac-task-input').length > 0) {
      form.find('.ac-task-input').val(task);
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

  $('[grid-draggable]').each(function () {
    var $this = $(this);

    if ($this.data('sortable-init')) {
      return;
    }

    $this.data('sortable-init', true);

    var options = nestedSortOptions($this);
    options.onEnd = function (e) {
      if ($this.is('[data-nested-root]') || $this.closest('[data-nested-root]').length) {
        sortOnEndHandler(e, $this);
      }
    };

    Sortable.create($this[0], options);
  });

  $('[grid-dropable]').each(function () {
    var $this = $(this);

    if ($this.data('sortable-init')) {
      return;
    }

    $this.data('sortable-init', true);

    var options = nestedSortOptions($this);
    options.onEnd = function (e) {
      if ($this.is('[data-nested-root]') || $this.closest('[data-nested-root]').length) {
        sortOnEndHandler(e, $this);
      }
    };

    Sortable.create($this[0], options);
  });

  $('[grid-sortable]').each(function () {
    var $this = $(this);

    if ($this.data('sortable-init')) {
      return;
    }

    $this.data('sortable-init', true);

    Sortable.create($this[0], {
      animation: 100,
      draggable: '[draggable]',
      handle: '[handle]',
      sort: true,
      filter: '.sortable-disabled',
      chosenClass: 'active',
      onEnd: function (e) {
        sortOnEndHandler(e, $this);
      },
    });
  });
};
