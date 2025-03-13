$(document).ajaxComplete(function () {
  $('#header-menu').collapse('hide');
});

window.brightPjax = () => {
  $(document).on('click', '[data-pjax] a, a[data-pjax]', function (e) {
    if ($(this).data('nojax')) {
      return true;
    }

    var container = $(this).data('pjax-container') || '[data-pjax-container]';
    $(this).parents('[data-pjax]').find('a').removeClass('active');
    $(this).addClass('active');

    let url = $(this).attr('href');

    $.pjax.click(e, {
      container: container,
      url: url,
      timeout: 15000,
    });
  });

  $(document).on('pjax:end', function (xhr) {
    $(document).trigger('ajax:loaded');
  });

  $(document).on('pjax:success', function (event, data, status, xhr, options) {
    // Update PJAX content by removing extracted fragments
    //event.result = fragment(data);
  });

  function fragment(data) {
    let $response = $('<div>').html(data); // Convert response to jQuery object

    // Extract and replace fragments in the existing DOM
    $response.find('[fragment]').each(function () {
      let $this = $(this);
      let key = $this.attr('fragment');
      let content = $this.html();

      let targetElements = document.querySelectorAll(`[fragment="${key}"]`);
      targetElements.forEach((target) => {
        target.innerHTML = content;
      });

      if (targetElements.length) {
        $(this).remove(); // Remove fragment from PJAX response
      }
    });

    return $response.html();
  }

  $(document).on('pjax:success', function (xhr) {
    //replaceFragments(xhr);
  });

  function replaceFragments(xhr) {
    if (xhr.readyState === 4 && xhr.status === 200) {
      let parser = new DOMParser();
      let doc = parser.parseFromString(xhr.responseText, 'text/html'); // Use "text/xml" for XML responses
      let elements = doc.querySelectorAll('[fragment]'); // Change attribute if needed

      elements.forEach((el) => {
        let key = el.getAttribute('fragment');
        let value = el.innerHTML.trim();

        // Find matching elements in the current DOM and replace content
        let targetElements = document.querySelectorAll(`[fragment="${key}"]`);
        targetElements.forEach((target) => {
          target.innerHTML = value;
        });
      });
    }
  }
};
