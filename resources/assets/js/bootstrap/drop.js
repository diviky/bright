window.load_drop = () => {
  $(document).on('change', '[data-drop] input[type=file]', function (e) {
    e.preventDefault();
    var $this = $(this).parent().find('.drop-preview');
    var files = $(this).prop('files');
    drawPreview($this, files);
  });

  $(document).on('click', '[data-dropzone]', function () {
    $(this).next('input[type=file]').click();
  });

  $(document).on('dragover', '[data-drop]', function (e) {
    $(this).find('[data-dropzone]').addClass('drag-hover');
    e.preventDefault();
  });

  $(document).on('dragleave', '[data-drop]', function (e) {
    $(this).find('[data-dropzone]').removeClass('drag-hover');
    e.preventDefault();
  });

  $(document).on('drop', '[data-drop]', function (e) {
    e.preventDefault();
    var $this = $(this).find('.drop-preview');
    var files = e.originalEvent.dataTransfer.files;
    $(this).find("input[type='file']").prop('files', files);
    drawPreview($this, files);
  });
};

window.drawPreview = ($this, files) => {
  $this.empty();
  $this.show();
  for (var i = 0; i < files.length; i++) {
    var file = files[i];
    if (file.type.match('image/*')) {
      $this.append('<li data-toggle="tooltip" title="' + file.name + '"> <i class="fa fa-file-image-o"></i></li>');
    } else if (file.type.match('audio/*')) {
      $this.append('<li data-toggle="tooltip" title="' + file.name + '"> <i class="fa fa-file-audio-o"></i></li>');
    } else if (file.type.match('video/*')) {
      $this.append('<li data-toggle="tooltip" title="' + file.name + '"> <i class="fa fa-file-video-o"></i></li>');
    } else {
      $this.append('<li data-toggle="tooltip" title="' + file.name + '"> <i class="fa fa-file-o"></i></li>');
    }
  }
};
