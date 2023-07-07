function load_autocomplete() {
    if (!$.fn.selectize) {
        console.info('node install selectize --save');
        return;
    }

    $('[data-selectize]').selectize();

    $('[data-selectize-icon]').selectize({
        render: {
            option: function (data, escape) {
                var template = '<div>';

                if (data.image) {
                    template += '<span class="image">' + data.image + '</span>';
                }

                if (data.icon) {
                    template += '<span class="image"><img src="' + data.icon + '" alt=""/></span>';
                }

                template += '<span class="title">' + escape(data.text) + '</span>';
                template += '</div>';

                return template;
            },
            item: function (data, escape) {
                var template = '<div>';

                if (data.image) {
                    template += '<span class="image">' + data.image + '</span>';
                }

                if (data.icon) {
                    template += '<span class="image"><img src="' + data.icon + '" alt=""/></span>';
                }

                template += '<span class="title">' + escape(data.text) + '</span>';
                template += '</div>';

                return template;
            },
        },
    });

    $('[data-select-image]').each(function () {
        var $this = $(this);
        var url = $this.data('select-image');

        $this.select2({
            minimumInputLength: 3,
            maximumInputLength: 20,
            ajax: {
                url: url,
                delay: 250,
                processResults: function (data) {
                    // Tranforms the top-level key of the response object from 'items' to
                    // 'results'
                    return { results: data.rows };
                },
            },
            templateResult: function (data) {
                if (!data.id) {
                    return data.text;
                }

                var template = '';
                if (data.icon) {
                    template += '<span class="image"><img src="' + data.icon + '" alt=""/></span>';
                }

                if (data.image) {
                    template += '<span class="image">' + data.image + '</span>';
                }

                template += '<span class="title">' + data.text + '</span>';

                return $(template);
            },
        });
    });

    $('[data-selectize-ajax]').each(function () {
        var $this = $(this);
        var url = $this.data('selectize-ajax');
        var method = $this.data('method') || 'GET';

        $this.selectize({
            options: [],
            valueField: 'id',
            labelField: 'text',
            searchField: 'text',
            loadThrottle: null,
            closeAfterSelect: true,
            create: false,
            persist: false,
            load: function (query, callback) {
                if (!query.length) return callback();

                $.ajax({
                    url: url,
                    type: method,
                    dataType: 'json',
                    delay: 250,
                    data: { term: query },
                    error: function () {
                        callback();
                    },
                    success: function (res) {
                        callback(res.rows);
                    },
                });
            },
        });
    });

    $('[data-selectize-target]').each(function () {
        var xhr, source, $source, target, $target;

        var $this = $(this);
        var selector = $this.data('selectize-target');
        var url = $this.data('url');
        var method = $this.data('method') || 'GET';

        var options = {
            valueField: 'id',
            labelField: 'text',
            searchField: ['text'],
        };

        $target = $(selector);
        if (!$target || typeof $target == 'undefined') {
            return false;
        }

        if (!$target[0] || !$target[0].selectize) {
            $target = $target.selectize(options);
        }

        if (!$target[0] || !$target[0].selectize) {
            return false;
        }

        var selected = $target.data('selected');

        target = $target[0].selectize;

        $source = $this.selectize({
            valueField: 'id',
            labelField: 'text',
            searchField: ['text'],
            onChange: function (value) {
                if (!value.length) return;
                target.clearOptions();
                target.clear();
                target.disable();

                target.load(function (callback) {
                    xhr && xhr.abort();
                    xhr = $.ajax({
                        url: url.replace(':id', value),
                        method: method,
                        dataType: 'json',
                        data: { id: value },
                        success: function (results) {
                            target.clearOptions();
                            target.clear();
                            target.enable();

                            callback(results.rows);

                            if (selected) {
                                target.setValue(selected);
                            }
                        },
                        error: function () {
                            callback();
                        },
                    });
                });
            },
        });

        source = $source[0].selectize;
    });

    $('[data-selectize-optout]').each(function () {
        var xhr, source, $source, target, $target;

        var $this = $(this);
        var selector = $this.data('data-selectize-optout');
        var url = $this.data('url');
        var method = $this.data('method') || 'GET';

        var options = {
            valueField: 'id',
            labelField: 'text',
            searchField: ['text'],
        };

        $target = $(selector);
        if (!$target || typeof $target == 'undefined') {
            return false;
        }

        if (!$target[0] || !$target[0].selectize) {
            $target = $target.selectize(options);
        }

        if (!$target[0] || !$target[0].selectize) {
            return false;
        }

        var selected = $target.data('selected');

        target = $target[0].selectize;

        $source = $this.selectize({
            valueField: 'id',
            labelField: 'text',
            searchField: ['text'],
            onChange: function (value) {
                if (!value.length) return;

                if (value == 'tag' || value == 'sender') {
                    target.clearOptions();
                    target.clear();
                    target.settings.create = true;
                } else {
                    target.clearOptions();
                    target.clear();
                    target.settings.create = false;
                }

                target.disable();

                target.load(function (callback) {
                    xhr && xhr.abort();
                    xhr = $.ajax({
                        url: url.replace(':id', value),
                        method: method,
                        dataType: 'json',
                        data: { id: value },
                        success: function (results) {
                            target.clearOptions();
                            target.clear();
                            
                            if (value != 'all') {
                                target.enable();
                            }                            

                            if (!results.rows.length) {
                                results.rows  = [
                                {
                                    id: '',
                                    text: '---No results found---'
                                }
                                ];
                            }

                            callback(results.rows);

                            if (selected) {
                                target.setValue(selected);
                            }
                        },
                        error: function () {
                            callback();
                        },
                    });
                });
            },
        });

        source = $source[0].selectize;
    });
}
