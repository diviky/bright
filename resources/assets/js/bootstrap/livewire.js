document.addEventListener('livewire:initialized', () => {
  Livewire.on('component.rendered', () => {
    setTimeout(() => {
      $(document).trigger('ajax:loaded');
    }, 100);
  });

  Livewire.on('component.dehydrate', (event) => {
    $(document).trigger('ajax:loaded');

    setTimeout(() => {
      $(document).trigger('ajax:loaded');
    }, 100);
  });

  Livewire.on('fragment.change', (event) => {
    let task = event.task || 'submit';
    let $this = null;

    setTimeout(() => {
      var form = getForm($this);
      setTask(form, form, task);
      form.submit();
      removeTask(form, form);
    }, 100);
  });
});
