function load_app_filepond() {
    window.pond = function (selector) {
        FilePond.registerPlugin(FilePondPluginFileValidateSize, FilePondPluginFileValidateType);

        var form = selector.parents('form:eq(0)');
        var prefix = selector.attr('data-upload-prefix') || '';
        var accept = selector.attr('accept') || '';
        var size = selector.attr('size') || '500MB';

        if (prefix && typeof prefix === 'undefined') {
            prefix = '';
        }

        if (prefix && typeof prefix === 'string' && typeof prefix !== 'undefined') {
            prefix = '/' + prefix;
        }

        var options = {
            credits: false,
            maxFileSize: size,
            allowFileTypeValidation: true,
            server: {
                timeout: 99999999,
                revert: (uniqueFileId, load, error) => {
                    // Should remove the earlier created temp file here
                    fetch(prefix + '/upload/revert', {
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-Token': $('input[name="_token"]').val(),
                        },
                        method: 'delete',
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            filename: uniqueFileId,
                            prefix: prefix,
                        }),
                    })
                        .then(function (response) {
                            return response.json();
                        })
                        .then(function (json) {
                            if (json.status == 'OK') {
                                $(document.getElementById(uniqueFileId)).remove();
                            } else {
                                // Can call the error method if something is wrong, should exit after
                                error('error deleting the file');
                            }
                        });

                    // Should call the load method when done, no parameters required
                    load();
                },
                process: function (fieldName, file, metadata, load, error, progress, abort) {
                    var filepondRequest = new XMLHttpRequest();
                    var filepondFormData = new FormData();
                    fetch(prefix + '/upload/signed', {
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-Token': $('input[name="_token"]').val(),
                        },
                        method: 'post',
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            filename: metadata.fileInfo.filenameWithoutExtension,
                            extension: metadata.fileInfo.fileExtension,
                            prefix: prefix,
                            metadata: metadata,
                            accept: accept,
                        }),
                    })
                        .then(function (response) {
                            return response.json();
                        })
                        .then(function (json) {
                            if (json.disk != 's3') {
                                file.inputs = json.inputs;
                                // append the FormData() in the order below. Changing the order
                                // would result in 403 bad request error response from S3.
                                // No other fields are needed apart from these ones. Appending extra
                                // fields to the formData would also result in error responses from S3.
                                for (var field in file.inputs) {
                                    filepondFormData.append(field, file.inputs[field]);
                                }

                                filepondFormData.append('file', file, file.name);

                                let token = $('meta[name="csrf-token"]').attr('content');
                                filepondFormData.append('_token', token);
                                filepondRequest.open('POST', json.attributes.action);
                            } else {
                                filepondFormData.append('file', file, file.name);
                                filepondRequest.open('PUT', json.attributes.action);
                                filepondFormData = file;
                            }

                            filepondRequest.upload.onprogress = function (e) {
                                progress(e.lengthComputable, e.loaded, e.total);
                            };

                            filepondRequest.onload = function (response) {
                                if (filepondRequest.status >= 200 && filepondRequest.status < 300) {
                                    load(json.key);
                                    var input =
                                        '<input type="hidden" name="filepond[]" id="' +
                                        json.key +
                                        '" value="' +
                                        fieldName +
                                        '" />';

                                    form.append(input);

                                    for (var field in file.inputs) {
                                        input =
                                            '<input type="hidden" name="' +
                                            field +
                                            '" value="' +
                                            file.inputs[field] +
                                            '" />';
                                        form.append(input);
                                    }

                                    let auto = selector.attr('auto-submit');
                                    if (auto && auto !== undefined) {
                                        setTimeout(function () {
                                            form.submit();
                                        }, 2000);
                                    }
                                } else {
                                    error('oh no');
                                }
                                return json.key;
                            };

                            filepondRequest.onerror = function (response) {
                                notify({
                                    type: 'error',
                                    text: 'unable to upload the file',
                                });
                            };

                            filepondRequest.onreadystatechange = function (response) {
                                if (filepondRequest.status === 422) {
                                    var jsonResponse = JSON.parse(filepondRequest.responseText);
                                    notify({
                                        type: 'error',
                                        text: jsonResponse.message,
                                    });
                                }
                            };

                            filepondRequest.send(filepondFormData);
                        });
                    return {
                        abort: function () {
                            filepondRequest.abort();
                            abort();
                        },
                    };
                },
            },
        };

        if ($.metadata && selector) {
            options = $.extend({}, options, selector.metadata({ type: 'html5' }));
        }

        const pond = new FilePond.create(selector[0], options);

        pond.on('addfile', function (error, file) {
            if (error) {
                return;
            }
            // Set file metadata here in order to retrieve it in the custom process function
            // file attributes like fileExtension and filenameWithoutExtension
            // are not availabe in the file object in the custom process function
            file.setMetadata('fileInfo', {
                filenameWithoutExtension: file.filenameWithoutExtension,
                fileExtension: file.fileExtension,
            });
        });

        window['ponds'] = [];
        let id = selector.attr('id');
        if (id && typeof id === 'string' && typeof id !== 'undefined') {
            var t = {
                pond: pond,
                form: form,
                selector: selector,
            };

            window['ponds'][id] = t;

            return t;
        }

        return pond;
    };

    $('[data-filepond]').each(function (key) {
        window.pond($(this));
    });
}
