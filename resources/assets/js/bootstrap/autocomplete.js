window.load_autocomplete = () => {
  function wireSet(target) {
    if (wired()) {
      try {
        // Handle wire:model.live
        if (target.hasAttribute('wire:model.live')) {
          const modelName = target.getAttribute('wire:model.live');
          wired().$set(modelName, target.value);
        } else {
          let name = target.hasAttribute('model') ? target.getAttribute('model') : target.name;
          if (wired().$get(name) !== undefined) {
            wired().$set(name, target.value);
          }
        }
      } catch (e) {
        //console.error(e);
      }
    }
  }

  if ($.fn.select2) {
    $('[data-select]').on('change', function (e) {
      wireSet(e.target);
    });

    $('[tokenizer]').on('change', function (e) {
      wireSet(e.target);
    });

    $('[data-select-ajax]').on('change', function (e) {
      wireSet(e.target);
    });

    $('[data-select-image]').on('change', function (e) {
      wireSet(e.target);
    });

    $('[data-select-target]').on('change', function (e) {
      wireSet(e.target);
    });

    $(document).on('click', '[data-select-refetch]', function (e) {
      let selector = $($(this).attr('data-select-refetch'));
      fetchSelect2Data(selector, selector);
      e.preventDefault();
    });

    function fetchSelect2Data($this, selector) {
      var xhr;
      var url = $this.data('select-fetch') || $this.data('select-refetch');
      var method = $this.data('fetch-method') || 'GET';
      var selected = $this.data('selected');
      selected = selected ? selected.toString().split(',') : [];

      var labelField = $this.attr('label-field') || $this.data('label-field') || 'text';
      var valueField = $this.attr('value-field') || $this.data('value-field') || 'id';
      var placeholder = $this.attr('placeholder') || null;
      var data = $this.data('post-data') || {};

      if (url && selector && selector.length > 0) {
        selector.prop('disabled', true);

        xhr && xhr.abort();
        xhr = $.ajax({
          url: url,
          data: data,
          method: method,
          dataType: 'json',
          success: function (results) {
            selector.val(null).empty();
            selector.prop('disabled', false);

            if (placeholder) {
              var option = new Option(placeholder, '');
              selector.append(option);
            }

            // if paginated results
            let records = results.rows?.links ? results.rows.data : results.rows;

            $.map(records, function (data) {
              let value = data[valueField].toString();
              let isSelected = jQuery.inArray(value, selected) !== -1 ? true : false;
              var option = new Option(data[labelField], value, isSelected, isSelected);
              selector.append(option);
            });

            selector.trigger('change');
          },
          error: function () {
            selector.val(null).empty().trigger('change');
            selector.prop('disabled', false);
          },
        });
      }
    }

    $('[data-select]').each(function () {
      var $this = $(this);

      if (!$this.data('select2')) {
        let selector = $this.select2({
          dropdownParent: $this.parent(),
          minimumResultsForSearch: 10,
        });

        fetchSelect2Data($this, selector);
      }
    });

    $('[tokenizer]').each(function () {
      var $this = $(this);
      if (!$this.data('select2')) {
        let selector = $this.select2({
          dropdownParent: $this.parent(),
          tags: true,
          tokenSeparators: [',', ' '],
          createTag: function (params) {
            var term = params.term;
            if (term === '') {
              return null;
            }

            return {
              id: term,
              text: term,
              new: true, // add additional parameters
            };
          },
        });

        fetchSelect2Data($this, selector);
      }
    });

    $('[data-select-ajax]').each(function () {
      var $this = $(this);
      if (!$this.data('select2')) {
        var url = $this.data('select-ajax');

        var labelField = $this.attr('label-field') || $this.data('label-field') || 'text';
        var valueField = $this.attr('value-field') || $this.data('value-field') || 'id';
        var data = $this.data('post-data') || {};

        let selector = $this.select2({
          minimumInputLength: 3,
          maximumInputLength: 20,
          dropdownParent: $this.parent(),
          ajax: {
            url: url,
            data: function (params) {
              data['q'] = params.term;
              data['term'] = params.term;
              return data;
            },
            delay: 250,
            processResults: function (data) {
              var rows = $.map(data.rows, function (obj) {
                obj.id = obj[valueField];
                obj.text = obj[labelField];

                return obj;
              });

              // Tranforms the top-level key of the response object from 'items' to 'results'
              return {
                results: rows,
              };
            },
          },
          templateResult: function (data) {
            if (!data.id) {
              return data.text;
            }

            var template = '<div class="item">';

            if (data.html) {
              template += '<span class="item-text">' + data.html + '</span>';
            }

            if (data.image) {
              template += '<span class="item-image">' + data.image + '</span>';
            }

            if (data.icon) {
              template +=
                '<span class="avatar avatar-xs item-icon" style="background-image: url(' +
                data.icon +
                ')" alt=""/></span>';
            }

            template += '<span class="item-title">' + data.text + '</span>';
            template += '</div>';

            return $(template);
          },
        });

        fetchSelect2Data($this, selector);
      }
    });

    $('[data-select-image]').each(function () {
      var $this = $(this);
      if (!$this.data('select2')) {
        var url = $this.data('select-image');
        var data = $this.data('post-data') || {};
        var method = $this.data('method') || 'GET';

        var labelField = $this.attr('label-field') || $this.data('label-field') || 'text';
        var valueField = $this.attr('value-field') || $this.data('value-field') || 'id';

        let selector = $this.select2({
          minimumInputLength: 3,
          maximumInputLength: 20,
          ajax: {
            url: url,
            data: data,
            method: method,
            delay: 250,
            processResults: function (data) {
              var rows = $.map(data.rows, function (obj) {
                obj.id = obj[valueField];
                obj.text = obj[labelField];

                return obj;
              });

              // Tranforms the top-level key of the response object from 'items' to 'results'
              return {
                results: rows,
              };
            },
          },
          templateResult: function (data) {
            if (!data.id) {
              return data.text;
            }

            var template = '<div class="item">';

            if (data.html) {
              template += '<span class="item-text">' + data.html + '</span>';
            }

            if (data.image) {
              template += '<span class="item-image">' + data.image + '</span>';
            }

            if (data.icon) {
              template +=
                '<span class="avatar avatar-xs item-icon" style="background-image: url(' +
                data.icon +
                ')" alt=""/></span>';
            }

            template += '<span class="item-title">' + data.text + '</span>';
            template += '</div>';

            return $(template);
          },
        });

        fetchSelect2Data($this, selector);
      }
    });

    $('[data-select-target]').each(function () {
      var $this = $(this);
      if (!$this.data('select2')) {
        let selector = $this.select2({
          dropdownParent: $this.parent(),
          minimumResultsForSearch: 5,
        });

        selector.on('change.select2', function () {
          loadSelectTargetValues($this, this.value);
        });

        //if it's selected value by default
        let val = $this.val();
        if (val !== undefined && val !== null && val.length > 0) {
          loadSelectTargetValues($this, val);
        }

        fetchSelect2Data($this, selector);
      }
    });

    function loadSelectTargetValues($this, value) {
      var xhr;

      var selector = $this.data('select-target');

      if (!selector) {
        return false;
      }

      next = $(selector);
      var url = $this.data('url');
      var method = $this.data('method') || 'GET';
      var data = $this.data('post-data') || {};

      if (typeof data === 'string') {
        data = data.replace(':id', value);
      } else {
        for (const [key, value] of Object.entries(data)) {
          data[key] = value.toString().replace(':id', value);
        }
      }

      var selected = next.data('selected');
      selected = selected ? selected.toString().split(',') : [];

      var labelField = next.attr('label-field') || next.data('label-field') || 'text';
      var valueField = next.attr('value-field') || next.data('value-field') || 'id';
      var nameField = $this.data('name-field') || 'name';
      var placeholder = next.attr('placeholder') || null;

      var nextdata = next.data('post-data') || {};

      if (typeof nextdata === 'string') {
        nextdata = nextdata.replace(':' + nameField, value);
      }

      nextdata[nameField] = value;

      for (const [key, value] of Object.entries(nextdata)) {
        nextdata[key] = value.toString().replace(':' + nameField, value);
      }

      next.attr('data-post-data', JSON.stringify(nextdata));
      next.prop('disabled', true);

      xhr && xhr.abort();
      xhr = $.ajax({
        url: url.replace(':id', value),
        method: method,
        data: data,
        dataType: 'json',
        success: function (results) {
          next.val(null).empty();
          next.prop('disabled', false);

          if (placeholder) {
            var option = new Option(placeholder, '');
            next.append(option);
          }

          // if paginated results
          let records = results.rows?.links ? results.rows.data : results.rows;

          $.map(records, function (data) {
            let value = data[valueField].toString();
            let isSelected = jQuery.inArray(value, selected) !== -1 ? true : false;
            var option = new Option(data[labelField], value, isSelected, isSelected);
            next.append(option);
          });

          next.trigger('change');
        },
        error: function () {
          next.val(null).empty().trigger('change');
          next.prop('disabled', false);
        },
      });
    }
  }

  if ($.fn.selectize) {
    function fetchSelectizeData($this, selector) {
      let xhr;
      var url = $this.data('selectize-fetch');
      var method = $this.data('fetch-method') || 'GET';
      var selected = $this.data('selected');
      var data = $this.data('post-data') || {};

      if (url && selector[0] && selector[0].selectize) {
        var control = selector[0].selectize;

        control.clearOptions();
        control.clear();
        control.disable();

        control.load(function (callback) {
          xhr && xhr.abort();
          xhr = $.ajax({
            url: url,
            method: method,
            data: data,
            dataType: 'json',
            success: function (results) {
              control.enable();

              callback(results.rows);

              if (selected) {
                control.setValue(selected);
              }
            },
            error: function () {
              control.enable();
              callback();
            },
          });
        });
      }
    }

    $(document).on('click', '[data-selectize-refetch]', function (e) {
      let selector = $($(this).attr('data-selectize-refetch'));
      fetchSelectizeData(selector, selector);
      e.preventDefault();
    });

    $('[data-selectize]').each(function () {
      var $this = $(this);
      var labelField = $this.attr('label-field') || $this.data('label-field') || 'text';
      var valueField = $this.attr('value-field') || $this.data('value-field') || 'id';
      var searchField = $this.data('search-field') || ['text'];

      let selector = $this.selectize({
        valueField: valueField,
        labelField: labelField,
        searchField: searchField,
      });

      fetchSelectizeData($this, selector);
    });

    $('[data-selectize-image]').each(function () {
      var $this = $(this);

      var labelField = $this.attr('label-field') || $this.data('label-field') || 'text';
      var valueField = $this.attr('value-field') || $this.data('value-field') || 'id';
      var searchField = $this.data('search-field') || ['text'];

      let selector = $this.selectize({
        valueField: valueField,
        labelField: labelField,
        searchField: searchField,
        render: {
          option: function (data, escape) {
            var template = '<div class="item">';

            if (data.html) {
              template += '<span class="item-text">' + data.html + '</span>';
            }

            if (data.image) {
              template += '<span class="item-image">' + data.image + '</span>';
            }

            if (data.icon) {
              template +=
                '<span class="avatar avatar-xs item-icon" style="background-image: url(' +
                data.icon +
                '" alt=""/></span>';
            }

            template += '<span class="item-title">' + escape(data.text) + '</span>';
            template += '</div>';

            return template;
          },
          item: function (data, escape) {
            var template = '<div class="item">';

            if (data.html) {
              template += '<span class="item-text">' + data.html + '</span>';
            }

            if (data.image) {
              template += '<span class="item-image">' + data.image + '</span>';
            }

            if (data.icon) {
              template +=
                '<span class="avatar avatar-xs item-icon" style="background-image: url(' +
                data.icon +
                '" alt=""/></span>';
            }

            template += '<span class="item-title">' + escape(data.text) + '</span>';
            template += '</div>';

            return template;
          },
        },
      });

      fetchSelectizeData($this, selector);
    });

    $('[data-selectize-tags]').each(function () {
      var $this = $(this);
      $this.selectize({
        delimiter: ',',
        persist: false,
        create: function (input) {
          return {
            value: input,
            text: input,
          };
        },
      });
    });

    $('[data-selectize-ajax]').each(function () {
      var $this = $(this);
      var url = $this.data('selectize-ajax');
      var method = $this.data('method') || 'GET';
      var labelField = $this.attr('label-field') || $this.data('label-field') || 'text';
      var valueField = $this.attr('value-field') || $this.data('value-field') || 'id';
      var searchField = $this.data('search-field') || ['text'];
      var data = $this.data('post-data') || {};

      let selector = $this.selectize({
        valueField: valueField,
        labelField: labelField,
        searchField: searchField,
        loadThrottle: null,
        closeAfterSelect: true,
        create: false,
        persist: false,
        load: function (query, callback) {
          if (!query.length) return callback();

          data[term] = query;

          $.ajax({
            url: url,
            type: method,
            dataType: 'json',
            delay: 250,
            data: data,
            error: function () {
              callback();
            },
            success: function (res) {
              callback(res.rows);
            },
          });
        },
      });

      fetchSelectizeData($this, selector);
    });

    $('[data-selectize-target]').each(function () {
      var $this = $(this);
      if (!$this[0] || !$this[0].selectize) {
        var target = getTargetNextSelectize($this);

        let labelField = $this.attr('label-field') || $this.data('label-field') || 'text';
        let valueField = $this.attr('value-field') || $this.data('value-field') || 'id';
        let searchField = $this.data('search-field') || ['text'];

        let selector = $this.selectize({
          valueField: valueField,
          labelField: labelField,
          searchField: searchField,
          onChange: function (value) {
            loadSelectizeTargetValues($this, target, value);
          },
        });

        //if it's selected value by default
        let val = $this.val();
        if (val !== undefined && val !== null && val.length > 0) {
          loadSelectizeTargetValues($this, target, val);
        }

        fetchSelectizeData($this, selector);
      }
    });

    function getTargetNextSelectize($this) {
      var selector = $this.data('selectize-target');

      if (!selector) {
        return false;
      }

      selector = $(selector);

      if (selector && typeof selector !== 'undefined' && selector.length > 0) {
        var labelField = selector.attr('label-field') || selector.data('label-field') || 'text';
        var valueField = selector.attr('value-field') || selector.data('value-field') || 'id';
        var searchField = selector.attr('search-field') || selector.data('search-field') || ['text'];

        var newTarget = getTargetNextSelectize(selector);

        var options = {
          valueField: valueField,
          labelField: labelField,
          searchField: searchField,
          onChange: function (value) {
            loadSelectizeTargetValues(selector, newTarget, value);
          },
        };

        var target;
        if (!selector[0] || !selector[0].selectize) {
          target = selector.selectize(options);
        }

        if (!target || !target[0] || !target[0].selectize) {
          return false;
        }
      }

      return selector;
    }

    function loadSelectizeTargetValues($this, $target, value) {
      var xhr;
      if (!$target) {
        return false;
      }

      var control = $target[0].selectize;

      if (!control) {
        return false;
      }

      // Clear previous options and selection
      control.clearOptions();
      control.clear();
      if (value.length <= 0) {
        return true;
      }

      var url = $this.data('url');
      var method = $this.data('method') || 'GET';
      var selected = $target.data('selected');

      var nameField = $this.data('name-field') || 'name';
      var nextdata = $target.data('post-data') || {};
      nextdata[nameField] = value;

      $target.attr('data-post-data', JSON.stringify(nextdata));

      var data = $this.data('post-data') || {};
      data[nameField] = value;

      control.clearOptions();
      control.clear();
      control.disable();

      control.load(function (callback) {
        xhr && xhr.abort();
        xhr = $.ajax({
          url: url.replace(':id', value),
          method: method,
          dataType: 'json',
          data: data,
          success: function (results) {
            control.enable();

            callback(results.rows);

            if (selected) {
              control.setValue(selected);
            }
          },
          error: function () {
            control.enable();
            callback();
          },
        });
      });
    }
  }
};
