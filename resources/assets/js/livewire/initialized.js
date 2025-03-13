document.addEventListener('livewire:initialized', () => {
  var timer;
  Livewire.on('component.rendered', () => {
    timer || clearTimeout(timer);

    timer = setTimeout(() => {
      $(document).trigger('ajax:loaded');
    }, 100);
  });

  Livewire.on('component.dehydrate', (event) => {
    $(document).trigger('ajax:loaded');
    timer || clearTimeout(timer);

    timer = setTimeout(() => {
      $(document).trigger('ajax:loaded');
    }, 100);
  });

  Livewire.on('fragment.change', (event) => {
    let task = event.task || 'submit';
    let $this = null;

    timer || clearTimeout(timer);

    timer = setTimeout(() => {
      var form = getForm($this);
      setTask(form, form, task);
      form.submit();
      removeTask(form, form);
    }, 100);
  });
});
