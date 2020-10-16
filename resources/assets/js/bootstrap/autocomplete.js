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
                    template += '<span class="image"><img src="' + data.icon +
                        '" alt=""/></span>';
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
                    template += '<span class="image"><img src="' + data.icon +
                        '" alt=""/></span>';
                }

                template += '<span class="title">' + escape(data.text) + '</span>';
                template += '</div>';

                return template;
            }
        }
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
                }
            },
            templateResult: function (data) {
                if (!data.id) {
                    return data.text;
                }

                var template = '';
                if (data.icon) {
                    template += '<span class="image"><img src="' + data.icon +
                        '" alt=""/></span>';
                }

                if (data.image) {
                    template += '<span class="image">' + data.image + '</span>';
                }

                template += '<span class="title">' + data.text + '</span>';

                return $(template);
            }
        });
    });

    $('[data-selectize-ajax]').each(function () {
        var $this = $(this);
        var url = $this.data('selectize-ajax');

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
                    type: 'GET',
                    dataType: 'json',
                    delay: 250,
                    data: { term: query },
                    error: function () {
                        callback();
                    },
                    success: function (res) {
                        callback(res.rows);
                    }
                });
            }
        });
    });
}
