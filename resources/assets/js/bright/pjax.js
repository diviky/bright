window.brightPjax = () => {
  const prefetchCache = {};
  const hoverTimers = {};
  const prefetchPromises = {};
  const PREFETCH_DELAY = 200; // ms delay before starting prefetch (increased to reduce accidental triggers)
  const MAX_CACHE_SIZE = 50; // Maximum number of cached pages
  const CACHE_EXPIRY_TIME = 10000; // 10 seconds in milliseconds

  // Global prefetch configuration
  let globalPrefetchEnabled = true;

  function cleanupCache() {
    const cacheKeys = Object.keys(prefetchCache);
    if (cacheKeys.length > MAX_CACHE_SIZE) {
      // Remove oldest entries (simple FIFO)
      const toRemove = cacheKeys.slice(0, cacheKeys.length - MAX_CACHE_SIZE);
      toRemove.forEach((key) => delete prefetchCache[key]);
    }
  }

  function isExpired(cacheEntry) {
    return Date.now() - cacheEntry.timestamp > CACHE_EXPIRY_TIME;
  }

  function cleanupExpiredCache() {
    Object.keys(prefetchCache).forEach((url) => {
      if (isExpired(prefetchCache[url])) {
        delete prefetchCache[url];
      }
    });
  }

  function prefetchPage(url, container) {
    // Clean up expired cache before checking
    cleanupExpiredCache();

    if (prefetchCache[url] || prefetchPromises[url]) return; // already prefetched or prefetching

    // Create timeout controller for browsers that don't support AbortSignal.timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 2000);

    // Mark as prefetching to avoid duplicate requests
    prefetchPromises[url] = fetch(url, {
      method: 'GET',
      headers: {
        'X-PJAX': 'true',
        'X-PJAX-Container': container,
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'text/html, application/xhtml+xml, application/xml;q=0.9, */*;q=0.8',
      },
      signal: controller.signal,
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.text();
      })
      .then((data) => {
        clearTimeout(timeoutId);
        // Store the raw HTML response in cache (don't apply it yet)
        prefetchCache[url] = {
          content: data,
          timestamp: Date.now(),
        };
        delete prefetchPromises[url];
        cleanupCache();

        // Set expiry timer for this cache entry
        setTimeout(() => {
          if (prefetchCache[url]) {
            delete prefetchCache[url];
          }
        }, CACHE_EXPIRY_TIME);
      })
      .catch((error) => {
        clearTimeout(timeoutId);
        delete prefetchPromises[url];
        if (error.name !== 'AbortError') {
          console.warn('PJAX prefetch failed for:', url, error.message);
        }
      });
  }

  $(document).on('mouseenter', '[data-pjax] a, a[data-pjax]', function (e) {
    if ($(this).data('nojax') || $(this).attr('nojax')) {
      return true;
    }

    // Check if prefetch is enabled (per-element or global setting)
    const elementPrefetch = $(this).attr('prefetch');
    let prefetchEnabled;

    if (elementPrefetch === 'true' || elementPrefetch === '') {
      // Explicitly enabled or empty attribute
      prefetchEnabled = true;
    } else if (elementPrefetch === 'false') {
      // Explicitly disabled
      prefetchEnabled = false;
    } else if (elementPrefetch === undefined) {
      // No prefetch attribute, use global setting
      prefetchEnabled = globalPrefetchEnabled;
    } else {
      // Any other value, default to global setting
      prefetchEnabled = globalPrefetchEnabled;
    }

    if (!prefetchEnabled) return true;

    const url = $(this).attr('href');
    const container = $(this).data('pjax-container') || '[data-pjax-container]';

    // Check if cache exists and is not expired
    if (prefetchCache[url] && !isExpired(prefetchCache[url])) return;
    if (prefetchPromises[url]) return;
    if (!url || !container) return;

    // Start hover timer with increased delay to reduce accidental triggers
    hoverTimers[url] = setTimeout(() => {
      prefetchPage(url, container);
      delete hoverTimers[url];
    }, PREFETCH_DELAY);
  });

  $(document).on('mouseleave', '[data-pjax] a, a[data-pjax]', function () {
    const url = $(this).attr('href');

    // Clear hover timer
    if (hoverTimers[url]) {
      clearTimeout(hoverTimers[url]);
      delete hoverTimers[url];
    }
  });

  $(document).on('click', '[data-pjax] a, a[data-pjax]', function (e) {
    if ($(this).data('nojax') || $(this).attr('nojax')) {
      return true;
    }

    const container = $(this).data('pjax-container') || '[data-pjax-container]';
    $(this).parents('[data-pjax]').find('a').removeClass('active');
    $(this).addClass('active');

    let url = $(this).attr('href');

    // Check if cache exists and is not expired
    if (prefetchCache[url] && !isExpired(prefetchCache[url])) {
      e.preventDefault();

      const cachedData = prefetchCache[url].content;

      // Parse the cached HTML response to extract the container content
      let $response = $('<div>').html(cachedData);
      let $containerContent = $response.find(container);

      if ($containerContent.length) {
        // Replace the container content with the cached content
        $(container).html($containerContent.html());
      } else {
        // Fallback: replace entire container with cached response
        $(container).html(cachedData);
      }

      // Clear cache after use (one-time use)
      delete prefetchCache[url];

      // Update browser history and trigger events
      window.history.pushState({}, '', url);
      $(document).trigger('pjax:success', [cachedData, 'success', null, { container: container, url: url }]);
      $(document).trigger('pjax:end');
    } else if (prefetchPromises[url]) {
      // Prefetch is in progress, wait for it to complete
      e.preventDefault();

      prefetchPromises[url]
        .then((data) => {
          // Use the prefetched data
          if (prefetchCache[url] && !isExpired(prefetchCache[url])) {
            const cachedData = prefetchCache[url].content;

            // Parse the cached HTML response to extract the container content
            let $response = $('<div>').html(cachedData);
            let $containerContent = $response.find(container);

            if ($containerContent.length) {
              // Replace the container content with the cached content
              $(container).html($containerContent.html());
            } else {
              // Fallback: replace entire container with cached response
              $(container).html(cachedData);
            }

            // Clear cache after use
            delete prefetchCache[url];

            // Update browser history and trigger events
            window.history.pushState({}, '', url);
            $(document).trigger('pjax:success', [cachedData, 'success', null, { container: container, url: url }]);
            $(document).trigger('pjax:end');
          } else {
            // Prefetch failed or expired, fall back to regular PJAX
            window.location.href = url;
          }
        })
        .catch((error) => {
          // Prefetch failed, fall back to regular navigation
          console.warn('Prefetch failed during click, falling back to regular navigation:', error);
          window.location.href = url;
        });
    } else {
      // Clean up expired cache if it exists
      if (prefetchCache[url] && isExpired(prefetchCache[url])) {
        delete prefetchCache[url];
      }

      $.pjax.click(e, {
        container: container,
        url: url,
        timeout: 15000,
      });
    }
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

  // Expose methods for debugging and manual cache management
  return {
    getCache: () => prefetchCache,
    clearCache: () => {
      Object.keys(prefetchCache).forEach((key) => delete prefetchCache[key]);
    },
    prefetch: prefetchPage,
    get prefetchEnabled() {
      return globalPrefetchEnabled;
    },
    set prefetchEnabled(value) {
      globalPrefetchEnabled = !!value;
    },
  };
};
