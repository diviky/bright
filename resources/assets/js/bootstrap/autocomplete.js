function load_autocomplete() {
    if ($.fn.select2) {
        $('[data-select]').each(function () {
            var $this = $(this);
            var xhr;

            let selector = $this.select2({
                dropdownParent: $this.parent(),
                minimumResultsForSearch: 5,
            });

            var url = $this.data('select-fetch');
            var method = $this.data('fetch-method') || 'GET';
            var selected = $this.data('selected');
            selected = selected ? selected.toString().split(',') : [];

            var labelField = $this.data('label-field') || 'text';
            var valueField = $this.data('value-field') || 'id';
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

                        $.map(results.rows, function (data) {
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
        });

        $('[tokenizer]').each(function () {
            var $this = $(this);
            var xhr;

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

            var url = $this.data('select-fetch');
            var method = $this.data('fetch-method') || 'GET';
            var selected = $this.data('selected');
            selected = selected ? selected.toString().split(',') : [];

            var labelField = $this.data('label-field') || 'text';
            var valueField = $this.data('value-field') || 'id';
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

                        $.map(results.rows, function (data) {
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
        });

        $('[data-select-ajax]').each(function () {
            var $this = $(this);
            var url = $this.data('select-ajax');
            var xhr;

            var labelField = $this.data('label-field') || 'text';
            var valueField = $this.data('value-field') || 'id';
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

            var url = $this.data('select-fetch');
            var method = $this.data('fetch-method') || 'GET';
            var selected = $this.data('selected');
            var data = $this.data('post-data') || {};

            selected = selected ? selected.toString().split(',') : [];

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

                        $.map(results.rows, function (data) {
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
        });

        $('[data-select-image]').each(function () {
            var $this = $(this);
            var url = $this.data('select-image');
            var data = $this.data('post-data') || {};
            var method = $this.data('method') || 'GET';

            var xhr;

            var labelField = $this.data('label-field') || 'text';
            var valueField = $this.data('value-field') || 'id';

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

            var url = $this.data('select-fetch');
            var method = $this.data('fetch-method') || 'GET';
            var selected = $this.data('selected');
            selected = selected ? selected.toString().split(',') : [];
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

                        $.map(results.rows, function (data) {
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
        });

        $('[data-select-target]').each(function () {
            var $this = $(this);
            var xhr;

            let selector = $this.select2({
                dropdownParent: $this.parent(),
                minimumResultsForSearch: 5,
            });

            selector.on('change.select2', function () {
                loadSelectTargetValues($this, this.value);
            });

            var url = $this.data('select-fetch');
            var method = $this.data('fetch-method') || 'GET';
            var selected = $this.data('selected');
            selected = selected ? selected.toString().split(',') : [];

            var labelField = $this.data('label-field') || 'text';
            var valueField = $this.data('value-field') || 'id';
            var data = $this.data('post-data') || {};

            if (url && selector && selector.length > 0) {
                selector.prop('disabled', true);

                xhr && xhr.abort();
                xhr = $.ajax({
                    url: url,
                    method: method,
                    data: data,
                    dataType: 'json',
                    success: function (results) {
                        selector.val(null).empty();
                        selector.prop('disabled', false);

                        $.map(results.rows, function (data) {
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

            var selected = next.data('selected');
            selected = selected ? selected.toString().split(',') : [];

            var labelField = next.data('label-field') || 'text';
            var valueField = next.data('value-field') || 'id';
            var nameField = $this.data('name-field') || 'name';
            var placeholder = next.attr('placeholder') || null;

            var nextdata = next.data('post-data') || {};
            nextdata[nameField] = value;

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

                    $.map(results.rows, function (data) {
                        let value = data[valueField]?.toString();
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
        $('[data-selectize]').each(function () {
            var $this = $(this);
            var xhr;
            var url = $this.data('selectize-ajax');
            var method = $this.data('method') || 'GET';
            var labelField = $this.data('label-field') || 'text';
            var valueField = $this.data('value-field') || 'id';
            var searchField = $this.data('search-field') || ['text'];

            let selector = $this.selectize({
                valueField: valueField,
                labelField: labelField,
                searchField: searchField,
            });

            var url = $this.data('selectize-fetch');
            var method = $this.data('fetch-method') || 'GET';
            var selected = $this.data('selected');
            var data = $this.data('post-data') || {};

            if (url && selector[0] && selector[0].selectize) {
                var control = selector[0].selectize;

                control.disable();

                control.load(function (callback) {
                    xhr && xhr.abort();
                    xhr = $.ajax({
                        url: url,
                        method: method,
                        data: data,
                        dataType: 'json',
                        success: function (results) {
                            control.clearOptions();
                            control.clear();
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
        });

        $('[data-selectize-image]').each(function () {
            var $this = $(this);
            var xhr;

            var labelField = $this.data('label-field') || 'text';
            var valueField = $this.data('value-field') || 'id';
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

            var url = $this.data('selectize-fetch');
            var method = $this.data('fetch-method') || 'GET';
            var selected = $this.data('selected');
            var data = $this.data('post-data') || {};

            if (url && selector[0] && selector[0].selectize) {
                var control = selector[0].selectize;

                control.disable();

                control.load(function (callback) {
                    xhr && xhr.abort();
                    xhr = $.ajax({
                        url: url,
                        data: data,
                        method: method,
                        dataType: 'json',
                        success: function (results) {
                            control.clearOptions();
                            control.clear();
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
            var xhr;
            var url = $this.data('selectize-ajax');
            var method = $this.data('method') || 'GET';
            var labelField = $this.data('label-field') || 'text';
            var valueField = $this.data('value-field') || 'id';
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

            var url = $this.data('selectize-fetch');
            var method = $this.data('fetch-method') || 'GET';
            var selected = $this.data('selected');

            if (url && selector[0] && selector[0].selectize) {
                var control = selector[0].selectize;

                control.disable();

                control.load(function (callback) {
                    xhr && xhr.abort();
                    xhr = $.ajax({
                        url: url,
                        method: method,
                        data: data,
                        dataType: 'json',
                        success: function (results) {
                            control.clearOptions();
                            control.clear();
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
        });

        $('[data-selectize-target]').each(function () {
            var $this = $(this);
            var xhr;
            if (!$this[0] || !$this[0].selectize) {
                var target = getTargetNextSelectize($this);

                labelField = $this.data('label-field') || 'text';
                valueField = $this.data('value-field') || 'id';
                searchField = $this.data('search-field') || ['text'];

                let selector = $this.selectize({
                    valueField: valueField,
                    labelField: labelField,
                    searchField: searchField,
                    onChange: function (value) {
                        if (!value.length) return;
                        loadSelectizeTargetValues($this, target, value);
                    },
                });

                var url = $this.data('selectize-fetch');
                var method = $this.data('fetch-method') || 'GET';
                var selected = $this.data('selected');
                var data = $this.data('post-data') || {};

                if (url && selector[0] && selector[0].selectize) {
                    var control = selector[0].selectize;

                    control.disable();

                    control.load(function (callback) {
                        xhr && xhr.abort();
                        xhr = $.ajax({
                            url: url,
                            method: method,
                            data: data,
                            dataType: 'json',
                            success: function (results) {
                                control.clearOptions();
                                control.clear();
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
        });

        function getTargetNextSelectize($this) {
            var selector = $this.data('selectize-target');

            if (!selector) {
                return false;
            }

            selector = $(selector);

            if (selector && typeof selector !== 'undefined' && selector.length > 0) {
                var labelField = selector.data('label-field') || 'text';
                var valueField = selector.data('value-field') || 'id';
                var searchField = selector.data('search-field') || ['text'];

                var newTarget = getTargetNextSelectize(selector);

                var options = {
                    valueField: valueField,
                    labelField: labelField,
                    searchField: searchField,
                    onChange: function (value) {
                        if (!value.length) return;
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

            var url = $this.data('url');
            var method = $this.data('method') || 'GET';
            var selected = $target.data('selected');

            var nameField = $this.data('name-field') || 'name';
            var nextdata = $target.data('post-data') || {};
            nextdata[nameField] = value;

            $target.attr('data-post-data', JSON.stringify(nextdata));

            var data = $this.data('post-data') || {};
            data[nameField] = value;
            control.disable();

            control.load(function (callback) {
                xhr && xhr.abort();
                xhr = $.ajax({
                    url: url.replace(':id', value),
                    method: method,
                    dataType: 'json',
                    data: data,
                    success: function (results) {
                        control.clearOptions();
                        control.clear();
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
}
