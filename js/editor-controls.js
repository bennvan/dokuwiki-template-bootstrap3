/* Keep the native editor controls and CodeMirror settings visually consistent. */
(function ($) {
    'use strict';

    function syncWrap() {
        var cm = window.plugin_codemirror && window.plugin_codemirror.cm;
        var enabled = cm ? cm.getOption('lineWrapping') : $('#wiki__text').attr('wrap') !== 'off';
        $('#size__ctl img[src$="/wrap.svg"], #size__ctl img[src$="/wrap.gif"]')
            .closest('button')
            .attr('aria-pressed', String(enabled))
            .toggleClass('active', enabled);
    }

    function styleControls() {
        var $controls = $('#size__ctl');
        $controls.children('img.cm-settings-button').each(function () {
            var $image = $(this);
            // Keep the plugin's original image and click handler. A real button
            // adds keyboard access and a larger click target around the icon.
            var $button = $('<button type="button" title="CodeMirror settings" aria-label="CodeMirror settings">');
            $image.wrap($button).attr('alt', '');
            $image.parent().on('click.bootstrap3Editor', function (event) {
                if (event.target === this) {
                    event.preventDefault();
                    event.stopPropagation();
                    $image.trigger('click');
                }
            });
        });
        $controls.children('button').addClass('btn btn-default').each(function () {
            var label = $(this).find('img').attr('alt');
            if (label) $(this).attr({'title': label, 'aria-label': label});
        });
        // The mobile settings panel can cover its trigger. Give it an explicit
        // close action, using the plugin's existing jQuery UI menu semantics.
        $('.cm-settings-menu').each(function () {
            var $menu = $(this);
            if ($menu.children('.bootstrap3-cm-close').length) return;
            function closeSettings(returnFocus) {
                $menu.menu('collapseAll', null, true).hide();
                if (returnFocus) $controls.find('.cm-settings-button').closest('button').trigger('focus');
            }
            $('<li class="bootstrap3-cm-close"><div>Close settings <span aria-hidden="true">×</span></div></li>')
                .prependTo($menu)
                .on('click.bootstrap3Editor', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    closeSettings(true);
                });
            $menu.on('menuselect.bootstrap3Editor', function (event, ui) {
                if (ui.item.hasClass('bootstrap3-cm-close')) ui.item.trigger('click');
            });
            // CodeMirror stops bubbling pointer events. Capture outside taps so
            // tapping the textbox dismisses the panel without stealing focus.
            document.addEventListener('pointerdown', function (event) {
                if (!$menu.is(':visible') || $menu[0].contains(event.target)) return;
                var $target = $(event.target);
                if ($target.closest('.cm-settings-button').length ||
                    $target.closest('button').find('.cm-settings-button').length) return;
                closeSettings(false);
            }, true);
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && $menu.is(':visible')) {
                    event.preventDefault();
                    closeSettings(true);
                }
            }, true);
            $menu.menu('refresh');
        });
        syncWrap();
    }

    $(function () {
        var controls = document.getElementById('size__ctl');
        if (!controls) return;
        styleControls();
        $(controls).on('click.bootstrap3Editor', 'button', syncWrap);
        // The settings image may arrive after DOM ready, including when the
        // user initially selects the native editor and no ready event fires.
        new MutationObserver(styleControls).observe(controls, {childList: true, subtree: true});
    });

    $(document).on('codemirror_ready.bootstrap3Editor', function (event, cm) {
        styleControls();
        cm.on('optionChange', function (editor, option) {
            if (option === 'lineWrapping') syncWrap();
        });
    }).on('codemirror_destroy.bootstrap3Editor', function () {
        setTimeout(syncWrap, 0);
    });
})(jQuery);
