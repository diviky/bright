window.setTask = ($this, form, task) => {
  var task = task || $this.data('task');
  var name = $this.data('task-name') || 'task';
  var input = form.find("input[name='" + name + "']");

  if (input.length > 0) {
    input.val(task);
  } else {
    $('<input/>', {
      name: name,
      class: 'task-input',
      value: task,
      type: 'hidden',
    }).appendTo(form);
  }

  let params = $this.data('params');

  if (params) {
    params = Object.fromEntries([...new URLSearchParams(params)]);
    for (const [key, value] of Object.entries(params)) {
      $('<input/>', {
        name: key,
        class: 'task-input',
        value: value,
        type: 'hidden',
      }).appendTo(form);
    }
  }
};

window.removeTask = ($this, form, task) => {
  var name = $this.data('task-name') || 'task';
  var input = form.find("input[name='" + name + "']");

  if (input.length > 0) {
    input.remove();
  }

  $('.task-input').remove();
};
