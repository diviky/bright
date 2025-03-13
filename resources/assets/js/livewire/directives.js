Livewire.directive('validate', ({ el, directive, component, cleanup }) => {
  let content = directive.expression;

  // The "directive" object gives you access to the parsed directive.
  // For example, here are its values for: wire:click.prevent="deletePost(1)"
  //
  // directive.raw = wire:click.prevent
  // directive.value = "click"
  // directive.modifiers = ['prevent']
  // directive.expression = "deletePost(1)"

  let defaults = {
    inputEvent: 'keyup blur change', // change, blur, keyup, null
    errorInputEvent: 'keyup', // change, blur, keyup, null
    effect: 'errors',
  };

  let options = {};
  if (el.hasAttribute('wire:validate.options')) {
    options = new Function(`return ${el.getAttribute('wire:validate.options')};`)();
  }

  let element = $(el);

  let validator = () => {
    if (!element.data('validator')) {
      element.validator({ ...defaults, ...options });
    }

    return element.data('validator');
  };

  validator();

  let isValid = () => {
    return validator().checkValidity();
  };

  let onSubmit = (e) => {
    if (!isValid()) {
      e.preventDefault();
      e.stopImmediatePropagation();

      return false;
    }

    if (content) {
      e.preventDefault();
      Alpine.evaluate(el, `$wire.${content}`, { scope: { $event: e } });
    }
  };

  el.addEventListener('submit', onSubmit);

  // Register any cleanup code inside `cleanup()` in the case
  // where a Livewire component is removed from the DOM while
  // the page is still active.
  cleanup(() => {
    el.removeEventListener('submit', onSubmit);
  });
});

Livewire.directive('form', ({ el, directive, component, cleanup }) => {
  let onSubmit = (e) => {
    e.preventDefault();

    let fields = formToAssocArray(el);
    component.$wire.call(directive.method, fields);
  };

  let formToAssocArray = (form) => {
    const assocArray = {};
    const elements = formToArray(form);

    elements.forEach(({ name, value, type }) => {
      const keys = name.match(/([^[\]]+)/g); // Extract keys from "user[files][profilePicture]"
      if (keys) {
        let current = assocArray;

        for (let i = 0; i < keys.length; i++) {
          const subKey = keys[i];
          const isArrayKey = i === keys.length - 1 && name.endsWith('[]'); // Detect empty array notation

          if (i === keys.length - 1) {
            // Last key, assign the value
            if (type === 'file') {
              if (!current[subKey]) {
                current[subKey] = isArrayKey ? [value] : value;
              } else if (Array.isArray(current[subKey])) {
                current[subKey].push(value);
              } else {
                current[subKey] = [current[subKey], value];
              }
            } else {
              if (isArrayKey) {
                // Ensure it's always an array
                if (!Array.isArray(current[subKey])) {
                  current[subKey] = current[subKey] !== undefined ? [current[subKey]] : [];
                }
                current[subKey].push(value);
              } else {
                if (current[subKey] !== undefined) {
                  if (!Array.isArray(current[subKey])) {
                    current[subKey] = [current[subKey]];
                  }
                  current[subKey].push(value);
                } else {
                  current[subKey] = value;
                }
              }
            }
          } else {
            // Intermediate key, ensure it's an object
            if (!current[subKey] || typeof current[subKey] !== 'object') {
              current[subKey] = {};
            }
            current = current[subKey];
          }
        }
      } else {
        // Regular field (not an associative array)
        if (type === 'file') {
          if (!assocArray[name]) {
            assocArray[name] = value;
          } else if (Array.isArray(assocArray[name])) {
            assocArray[name].push(value);
          } else {
            assocArray[name] = [assocArray[name], value];
          }
        } else if (assocArray[name] !== undefined) {
          if (!Array.isArray(assocArray[name])) {
            assocArray[name] = [assocArray[name]];
          }
          assocArray[name].push(value);
        } else {
          assocArray[name] = value;
        }
      }
    });

    return assocArray;
  };

  let getFields = (form) => {
    var formdata = new FormData();
    let a = formToArray(form);
    for (var i = 0; i < a.length; i++) {
      formdata.append(a[i].name, a[i].value);
    }

    return Object.fromEntries(formdata);
  };

  let formToArray = (form) => {
    const a = [];
    let els = form.querySelectorAll('*');

    // Convert NodeList to an Array
    if (els) {
      els = Array.from(els);
    }

    if (!els || !els.length) {
      return a;
    }

    for (let i = 0; i < els.length; i++) {
      const el = els[i];
      const n = el.name;
      if (!n || el.disabled) {
        continue;
      }

      const v = getFieldValue(el, true);
      if (Array.isArray(v)) {
        for (let j = 0; j < v.length; j++) {
          a.push({ name: n, value: v[j] });
        }
      } else if (el.type === 'file') {
        const files = el.files;
        if (files.length) {
          for (let j = 0; j < files.length; j++) {
            a.push({ name: n, value: files[j], type: el.type });
          }
        } else {
          a.push({ name: n, value: '', type: el.type });
        }
      } else if (v !== null && v !== undefined) {
        a.push({ name: n, value: v, type: el.type, required: el.required, el: el });
      }
    }

    return a;
  };

  function getFieldValue(el, successful) {
    if (successful === undefined) successful = true;

    const type = el.type,
      tag = el.tagName.toLowerCase();
    if (
      successful &&
      (!el.name ||
        el.disabled ||
        type === 'reset' ||
        type === 'button' ||
        ((type === 'checkbox' || type === 'radio') && !el.checked) ||
        ((type === 'submit' || type === 'image') && el.form && el.form.clk !== el))
    ) {
      return null;
    }

    if (tag === 'select') {
      const index = el.selectedIndex;
      if (index < 0) return null;
      const options = el.options;
      const one = type === 'select-one';
      const values = one ? null : [];
      const max = one ? index + 1 : options.length;
      for (let i = one ? index : 0; i < max; i++) {
        const option = options[i];
        if (
          option.selected &&
          !option.disabled &&
          (!option.parentNode.disabled || option.parentNode.tagName.toLowerCase() !== 'optgroup')
        ) {
          const value = option.value;
          if (one) return value;
          values.push(value);
        }
      }
      return values;
    }
    return el.value;
  }

  el.addEventListener('submit', onSubmit);

  // Register any cleanup code inside `cleanup()` in the case
  // where a Livewire component is removed from the DOM while
  // the page is still active.
  cleanup(() => {
    el.removeEventListener('submit', onSubmit);
  });
});
