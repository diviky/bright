window.loadAlpineData = () => {
  Alpine.data('search', () => ({
    search: '',
    clear() {
      this.focused = false;
      this.search = '';
    },
    reset() {
      this.clear();
    },
    focus() {
      this.focused = true;
      this.$refs.input.focus();
    },
    lookup() {
      let childs = this.$refs.source.querySelectorAll('[search-value');
      Array.from(childs).forEach((child) => {
        if (
          child.getAttribute('search-value') &&
          !child.getAttribute('search-value').match(new RegExp(this.search, 'i'))
        ) {
          child.classList.add('hide');
        } else {
          child.classList.remove('hide');
        }
      });
    },
  }));

  Alpine.data('dropdown', () => ({
    show: false,
    dispatched: false,
    close() {
      this.show = false;
    },
    open() {
      this.show = true;
    },
    toggle() {
      this.show = !this.show;
    },
    dispatch(event) {
      this.toggle();

      if (!this.dispatched) {
        this.dispatched = true;
        Livewire.dispatch(event);
      }
    },
  }));

  Alpine.directive('uppercase', (el, { value, expression }, { effect, evaluateLater, cleanup }) => {
    let templateFn = () => expression;
    let lastInputValue = '';

    queueMicrotask(() => {
      processInputValue(el, false);

      // Override x-model's initial value...
      if (el._x_model) el._x_model.set(el.value);
    });

    const controller = new AbortController();

    cleanup(() => {
      controller.abort();
    });

    el.addEventListener('input', () => processInputValue(el), {
      signal: controller.signal,
      // Setting this as a capture phase listener to ensure it runs
      // before wire:model or x-model added as a latent binding...
      capture: true,
    });

    // Don't "restoreCursorPosition" on "blur", because Safari
    // will re-focus the input and cause a focus trap.
    el.addEventListener('blur', () => processInputValue(el, false), {
      signal: controller.signal,
    });

    function processInputValue(el, shouldRestoreCursor = true) {
      let input = el.value;

      let template = templateFn(input);

      // If they hit backspace, don't process input.
      if (lastInputValue.length - el.value.length === 1) {
        return (lastInputValue = el.value);
      }

      let setInput = () => {
        lastInputValue = el.value = el.value.toUpperCase();
      };

      if (shouldRestoreCursor) {
        // When an input element's value is set, it moves the cursor to the end
        // therefore we need to track, estimate, and restore the cursor after
        // a change was made.
        let cursorPosition = el.selectionStart;
        el.setSelectionRange(cursorPosition, cursorPosition);
      }

      setInput();
    }
  }).before('model');

  Alpine.data('checkboxes', () => ({
    checkAll: false,
    checked: false,
    id: [],
    init() {
      this.$watch('checkAll', (value) => this.toggleAll());
      document.addEventListener('task', (event) => {
        let detail = event.detail;
        let task = detail.name;
        delete detail.name;

        if (task) {
          this.$dispatch('event.task.' + task, {
            id: this.id.filter(function (el) {
              return el != null && el != '';
            }),
            ...detail,
          });

          this.checkAll = false;
          this.toggleAll();
        }
      });

      document.addEventListener('modal', (event) => {
        let detail = event.detail;

        this.$dispatch('model.open', {
          data: {
            id: this.id.filter(function (el) {
              return el != null && el != '';
            }),
          },
          ...detail,
        });

        this.checkAll = false;
        this.toggleAll();
      });
    },
    toggleAll() {
      let checkboxes = this.$el.querySelectorAll('input[type=checkbox]');
      if (this.checkAll) {
        this.id = [...checkboxes].map((checkbox) => checkbox.value);
      } else {
        this.id = [];
      }
    },
    updateCheckAll() {
      const checkboxes = this.$el.querySelectorAll('input[type=checkbox]');
      this.checked = [...checkboxes].every((checkbox) => checkbox.checked);
    },
  }));
};
document.addEventListener('alpine:init', () => {
  loadAlpineData();
});
