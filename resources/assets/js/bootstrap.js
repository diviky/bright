jQuery.fn.bstooltip = jQuery.fn.tooltip;

jQuery(document).ready(function ($) {

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
        }
    });

    $('[data-toggle="tooltip"]').bstooltip();
    $('[data-toggle="popover"]').popover();

    $(document).on('click', '[data-toggle="tabs"] a', function (e) {
        e.preventDefault();
        $(this).tab('show');
        window.location.hash = this.hash;
        var scrollmem = $('body').scrollTop() || $('html').scrollTop();
        $('html,body').scrollTop(scrollmem);
    });

    var hash = window.location.hash;
    if (hash) {
        $('[data-toggle="tabs"] a[href="' + hash + '"]').tab('show');
    }

    $(window).on('hashchange', function () {
        var hash = window.location.hash;
        hash && $('[data-toggle="tabs"], a[href="' + hash + '"]').tab('show');
    });

    $(document).on('click', '[data-toggle="sidebar"]', function () {
        $(this).toggleClass('collapsed');
        var target = $(this).data('target');
        $(target).toggleClass('in');
    });

    $(document).on('click', '[data-toggle="backdrop"]', function () {
        var parent = $(this).parent();
        $(parent).removeClass('in');

        var target = parent.attr('id');
        $('[data-target="#' + target + '"]').addClass('collapsed');
    });

    $(document).on('click', '#search', function () {
        $(this).parents('div:first').toggleClass('active');
    });

    if ($.fn.lazyload) {
        $("img[data-original]").livequery(function () {
            $(this).lazyload({
                effect: "fadeIn"
            });
        });
    }

    if ($.fn.slimscroll) {
        $("div[role=scroll]").livequery(function () {
            $(this).slimscroll({
                height: 'auto',
                railVisible: true,
                size: '5px',
                wheelStep: 10
            });
        });
    }

    if (typeof ClipboardJS === 'function') {
        var clipboard = new ClipboardJS('[data-clipboard]');
        clipboard.on('success', function (e) {
            e.clearSelection();
            console.info('Action:', e.action);
            console.info('Text:', e.text);
            console.info('Trigger:', e.trigger);
            //showTooltip(e.trigger, 'Copied!');
        });
        clipboard.on('error', function (e) {
            console.error('Action:', e.action);
            console.error('Trigger:', e.trigger);
            //showTooltip(e.trigger, fallbackMessage(e.action));
        });
    }

    $('[data-bootstrap-select]').find('li').click(function (e) {
        e.preventDefault();
        var param = $(this).data("param");
        var concept = $(this).text();
        $(this).parent('div').find('[data-concept]').text(concept);
        $(this).parent('div').find('[data-param]').val(param);
    });

    if ($.fn.waypoint) {
        $('[data-waypoint]').livequery(function () {
            var $this = $(this);
            $this.waypoint({
                handler: function (direction) {
                    $this.toggleClass('navbar-inverse', direction == 'down');
                    $this.toggleClass('navbar-default', direction == 'up');
                    $this.toggleClass('sticky', direction == 'down');
                    $('body').toggleClass('sticky', direction == 'down');
                },
                offset: -50
            });
        });
    }

    $(window).on('scroll', function () {
        var scroll = $(window).scrollTop();
        $('[data-sticky]').each(function (index, el) {
            var height = parseInt($(this).data('sticky')) || 200;
            if (scroll < height) {
                $(this).removeClass("sticky");
                $('body').removeClass("sticky");
            } else {
                $(this).addClass("sticky");
                $('body').addClass("sticky");
            }
        });
        return false;
    });

    if ($.fn.chosen) {
        $('[data-chosen]').chosen({width:"auto"});
    }

    if ($.fn.tokenfield) {
        $('[data-tokenfield]').tokenfield();
    }
});

