window.brightPjax = () => {
  const prefetchCache = {};
  const hoverTimers = {};
  const prefetchPromises = {};
  const recentNavigations = {}; // Track recent navigations to prevent immediate re-prefetch
  const PREFETCH_DELAY = 200; // ms delay before starting prefetch (increased to reduce accidental triggers)
  const MAX_CACHE_SIZE = 50; // Maximum number of cached pages
  const CACHE_EXPIRY_TIME = 10000; // 10 seconds in milliseconds
  const NAVIGATION_COOLDOWN = 2000; // 2 seconds cooldown after navigation
  const PJAX_LINK_SELECTOR = '[data-pjax] a, a[data-pjax]';
  const PREFETCH_TIMEOUT = 10000; // 10 seconds in milliseconds

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

  function cleanupCacheEntry(url) {
    if (prefetchCache[url]) {
      delete prefetchCache[url];
    }
  }

  function markRecentNavigation(url) {
    recentNavigations[url] = Date.now();
    // Auto-cleanup after cooldown period
    setTimeout(() => {
      delete recentNavigations[url];
    }, NAVIGATION_COOLDOWN);
  }

  function isRecentlyNavigated(url) {
    if (!recentNavigations[url]) return false;
    return Date.now() - recentNavigations[url] < NAVIGATION_COOLDOWN;
  }

  function cleanupExpiredCache() {
    Object.keys(prefetchCache).forEach((url) => {
      if (isExpired(prefetchCache[url])) {
        delete prefetchCache[url];
      }
    });
  }

  function isPrefetchPromiseValid(url) {
    // Check if prefetch promise exists and is not in a rejected state
    return prefetchPromises[url] && prefetchPromises[url].then;
  }

  function normalizeUrl(url) {
    try {
      return new URL(url, window.location.origin).href;
    } catch {
      return url;
    }
  }

  function findInertiaLink(url) {
    const targetUrl = normalizeUrl(url);

    return Array.from(document.querySelectorAll('a[data-inertia]')).find((link) => {
      const href = link.getAttribute('href');

      if (!href || href === '#' || href === '') {
        return false;
      }

      return normalizeUrl(href) === targetUrl;
    });
  }

  function isInertiaHtml(html) {
    if (!html || typeof html !== 'string') {
      return false;
    }

    return (
      /<script[^>]+data-page=/i.test(html) ||
      /data-bright-inertia-page/i.test(html) ||
      /<div[^>]+id="app"[^>]*>\s*<\/div>/i.test(html)
    );
  }

  function isInertiaContent(contents) {
    if (!contents || !contents.length) {
      return false;
    }

    const $contents = contents.jquery ? contents : $(contents);

    return (
      $contents.filter('script[data-page]').length > 0 ||
      $contents.find('script[data-page]').length > 0 ||
      $contents.filter('[data-bright-inertia-page]').length > 0 ||
      $contents.find('[data-bright-inertia-page]').length > 0 ||
      ($contents.find('#app').length > 0 && $contents.find('script[data-page]').length > 0)
    );
  }

  function prefetchInertia(url) {
    if (typeof window.__inertiaPrefetch === 'function') {
      window.__inertiaPrefetch(url);
      return true;
    }

    return false;
  }

  function navigateInertia(url) {
    const targetUrl = normalizeUrl(url);

    if (typeof window.__inertiaNavigate === 'function') {
      window.__inertiaNavigate(url);
      return true;
    }

    // PJAX may have already updated the URL before we detect an Inertia page.
    if (normalizeUrl(window.location.href) === targetUrl) {
      window.location.reload();
      return false;
    }

    window.location.assign(url);
    return false;
  }

  function isInertiaResponse(response, html) {
    if (response && response.headers && response.headers.get('X-Inertia')) {
      return true;
    }

    return isInertiaHtml(html);
  }

  function applyNavigation(html, container, url) {
    if (isInertiaHtml(html)) {
      navigateInertia(url);
      return true;
    }

    applyHtmlToContainer(html, container);
    return false;
  }

  function resolvePjaxUrl(options, fallbackUrl) {
    return (options && (options.requestUrl || options.url)) || fallbackUrl || window.location.href;
  }

  function isPjaxAjaxOptions(options) {
    if (options.data && options.data._pjax) {
      return true;
    }

    if (Array.isArray(options.data)) {
      return options.data.some((entry) => entry && entry.name === '_pjax');
    }

    return false;
  }

  function interceptInertiaPjaxResponse() {
    $.ajaxPrefilter(function (options) {
      if (!isPjaxAjaxOptions(options)) {
        return;
      }

      const originalSuccess = options.success;

      options.success = function (data, textStatus, jqXHR) {
        const url = resolvePjaxUrl(options, jqXHR?.getResponseHeader?.('X-PJAX-URL'));

        if (isInertiaResponse(jqXHR, data)) {
          navigateInertia(url);
          return;
        }

        if (typeof originalSuccess === 'function') {
          return originalSuccess.apply(this, arguments);
        }
      };
    });
  }

  interceptInertiaPjaxResponse();

  function prefetchPage(url, container) {
    if (findInertiaLink(url)) {
      prefetchInertia(url);
      return;
    }

    // Clean up expired cache before checking
    cleanupExpiredCache();

    if (prefetchCache[url] || isPrefetchPromiseValid(url)) return; // already prefetched or prefetching

    // Don't prefetch if recently navigated to this URL
    if (isRecentlyNavigated(url)) return;

    // Create timeout controller for browsers that don't support AbortSignal.timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => {
      controller.abort();
      // Clear the promise immediately on abort to prevent race conditions
      delete prefetchPromises[url];
    }, PREFETCH_TIMEOUT);

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

        return response.text().then((data) => ({
          data,
          isInertia: isInertiaResponse(response, data),
        }));
      })
      .then(({ data, isInertia }) => {
        clearTimeout(timeoutId);

        if (isInertia) {
          prefetchInertia(url);
          return data;
        }

        // Store the raw HTML response in cache (don't apply it yet)
        prefetchCache[url] = {
          content: data,
          timestamp: Date.now(),
        };
        cleanupCache();

        // Set expiry timer for this cache entry
        setTimeout(() => {
          if (prefetchCache[url]) {
            delete prefetchCache[url];
          }
        }, CACHE_EXPIRY_TIME);

        // Return the data for promise consumers
        return data;
      })
      .catch((error) => {
        clearTimeout(timeoutId);
        if (error.name !== 'AbortError') {
          console.warn('PJAX prefetch failed for:', url, error.message);
        }
        // Clear the promise reference immediately on error/abort
        delete prefetchPromises[url];
        throw error; // Re-throw to propagate to promise consumers
      })
      .finally(() => {
        // Always clean up the promise reference (fallback)
        delete prefetchPromises[url];
      });
  }

  // -------------------------
  // Helper utilities
  // -------------------------
  function getCachedEntry(url) {
    if (!prefetchCache[url]) return null;
    if (isExpired(prefetchCache[url])) {
      delete prefetchCache[url];
      return null;
    }
    return prefetchCache[url];
  }

  function isPrefetchEnabled($this) {
    const elementPrefetch = $this.attr('prefetch');
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

    return prefetchEnabled;
  }

  function hasPjaxEnabled($this) {
    if ($this.data('nojax') || $this.attr('nojax') || $this.is('[data-inertia]')) {
      return false;
    }

    const url = $this.attr('href');

    if (!url || url === '#' || url === '') {
      return false;
    }

    return true;
  }

  function applyHtmlToContainer(html, container) {
    let $response = $('<div>').html(html);
    let $containerContent = $response.find(container);

    if ($containerContent.length) {
      $(container).html($containerContent.html());
    } else {
      $(container).html(html);
    }
  }

  function triggerPjaxLifecycle(html, container, url) {
    $(document).trigger('pjax:success', [html, 'success', null, { container: container, url: url }]);
    $(document).trigger('pjax:end');
  }

  $(document).on('mouseenter', PJAX_LINK_SELECTOR, function (e) {
    if (!hasPjaxEnabled($(this))) return;
    if (!isPrefetchEnabled($(this))) return;

    const container = $(this).data('pjax-container') || '[data-pjax-container]';
    const url = $(this).attr('href');

    // Use atomic cache check to prevent race conditions
    if (!url || !container) return;

    // Check if cache exists and is not expired (atomic check)
    const cachedEntry = getCachedEntry(url);
    if (cachedEntry) return; // Valid cache exists, no need to prefetch

    // Check if already prefetching
    if (isPrefetchPromiseValid(url)) return;

    // Start hover timer with increased delay to reduce accidental triggers
    hoverTimers[url] = setTimeout(() => {
      // Double-check before prefetching to avoid race conditions
      if (!getCachedEntry(url) && !isPrefetchPromiseValid(url)) {
        prefetchPage(url, container);
      }
      delete hoverTimers[url];
    }, PREFETCH_DELAY);
  });

  $(document).on('mouseleave', PJAX_LINK_SELECTOR, function () {
    const url = $(this).attr('href');

    // Clear hover timer
    if (hoverTimers[url]) {
      clearTimeout(hoverTimers[url]);
      delete hoverTimers[url];
    }
  });

  $(document).on('click', PJAX_LINK_SELECTOR, function (e) {
    if (!hasPjaxEnabled($(this))) return;
    if (!isPrefetchEnabled($(this))) return;

    const url = $(this).attr('href');

    const container = $(this).data('pjax-container') || '[data-pjax-container]';
    $(this).parents('[data-pjax]').find('a').removeClass('active');
    $(this).addClass('active');

    // Clear any pending hover timers for this URL to prevent race conditions
    if (hoverTimers[url]) {
      clearTimeout(hoverTimers[url]);
      delete hoverTimers[url];
    }

    // Atomic cache check to prevent race conditions
    const cachedEntry = getCachedEntry(url);
    if (cachedEntry) {
      e.preventDefault();
      const html = cachedEntry.content;

      if (applyNavigation(html, container, url)) {
        cleanupCacheEntry(url);
        markRecentNavigation(url);
        return;
      }

      window.history.pushState({ container: container, url: url }, '', url);
      triggerPjaxLifecycle(html, container, url);
      cleanupCacheEntry(url);
      markRecentNavigation(url);
      return;
    }

    // Check if prefetch is in progress and valid
    if (isPrefetchPromiseValid(url)) {
      NProgress.start();
      // Prefetch is in progress, wait for it to complete
      e.preventDefault();
      prefetchPromises[url]
        .then((html) => {
          NProgress.done();
          // Use the HTML data directly from the promise
          if (html) {
            if (applyNavigation(html, container, url)) {
              cleanupCacheEntry(url);
              markRecentNavigation(url);
              return;
            }

            window.history.pushState({ container: container, url: url }, '', url);
            triggerPjaxLifecycle(html, container, url);
            cleanupCacheEntry(url);
            markRecentNavigation(url);
          } else {
            // Prefetch failed, fall back to regular PJAX
            markRecentNavigation(url);
            window.location.href = url;
          }
        })
        .catch((error) => {
          NProgress.done();
          markRecentNavigation(url);
          window.location.href = url;
        });
      return;
    }

    // No cache and no prefetch in progress, use regular PJAX
    markRecentNavigation(url);
    $.pjax.click(e, {
      container: container,
      url: url,
      timeout: PREFETCH_TIMEOUT,
    });
  });

  $(document).on('pjax:end', function (xhr) {
    $(document).trigger('ajax:loaded');
  });

  // Prevent PJAX from injecting Inertia HTML (scripts won't run via .html()).
  function handlePjaxBeforeReplace(event, contents, options) {
    if (!isInertiaContent(contents)) {
      return;
    }

    event.preventDefault();
    navigateInertia(resolvePjaxUrl(options));
  }

  $(document).on('pjax:beforeReplace', handlePjaxBeforeReplace);
  $(document).on('pjax:beforeReplace', '[data-pjax-container]', handlePjaxBeforeReplace);

  // Store container in history state when regular PJAX navigates
  $(document).on('pjax:success', function (event, data, status, xhr, options) {
    const url = resolvePjaxUrl(options);

    if (isInertiaHtml(data) || (xhr && xhr.getResponseHeader && xhr.getResponseHeader('X-Inertia'))) {
      navigateInertia(url);
      return;
    }

    if (typeof window.__inertiaUnmount === 'function' && window.__inertiaBootstrapped) {
      window.__inertiaUnmount();
    }

    if (options && options.container && window.history.state) {
      // Update the current history state with container info
      const currentState = window.history.state || {};
      currentState.container = options.container;
      currentState.url = window.location.href;
      window.history.replaceState(currentState, '', window.location.href);
    }
  });

  // Handle browser back/forward button navigation
  window.addEventListener('popstate', function (e) {
    const url = window.location.href;

    // Get container from state if available, otherwise use default
    const container = (e.state && e.state.container) || '[data-pjax-container]';

    // Check cache first
    const cachedEntry = getCachedEntry(url);
    if (cachedEntry) {
      if (applyNavigation(cachedEntry.content, container, url)) {
        cleanupCacheEntry(url);
        markRecentNavigation(url);
        return;
      }

      triggerPjaxLifecycle(cachedEntry.content, container, url);
      cleanupCacheEntry(url);
      markRecentNavigation(url);
      return;
    }

    // If not in cache, fetch the page
    NProgress.start();

    fetch(url, {
      method: 'GET',
      headers: {
        'X-PJAX': 'true',
        'X-PJAX-Container': container,
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'text/html, application/xhtml+xml, application/xml;q=0.9, */*;q=0.8',
      },
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        return response.text().then((html) => ({
          html,
          isInertia: isInertiaResponse(response, html),
        }));
      })
      .then(({ html, isInertia }) => {
        NProgress.done();

        if (isInertia) {
          navigateInertia(url);
          markRecentNavigation(url);
          return;
        }

        applyHtmlToContainer(html, container);
        triggerPjaxLifecycle(html, container, url);
        markRecentNavigation(url);
      })
      .catch((error) => {
        NProgress.done();
        console.warn('PJAX popstate failed for:', url, error.message);
        // Fall back to full page reload
        window.location.href = url;
      });
  });

  // Expose methods for debugging and manual cache management
  return {
    getCache: () => prefetchCache,
    getPromises: () => prefetchPromises,
    getRecentNavigations: () => recentNavigations,
    clearCache: () => {
      Object.keys(prefetchCache).forEach((key) => delete prefetchCache[key]);
    },
    clearRecentNavigations: () => {
      Object.keys(recentNavigations).forEach((key) => delete recentNavigations[key]);
    },
    clearPrefetchPromises: () => {
      Object.keys(prefetchPromises).forEach((key) => delete prefetchPromises[key]);
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
