/** Media actions belong to the current file-list namespace, not the selected file. */
dw_template.mediaActions = function () {
    var $files = jQuery('#mediamanager__page div.filelist').first();
    var $tabs = $files.children('ul.tabs').first();
    if (!$tabs.length || $tabs.find('.bs-media-actions-tab').length) return;
    var labels = LANG.template.bootstrap3;
    var $link = jQuery('<a href="#"/>').text(labels.mediaActions);
    jQuery('<li class="bs-media-actions-tab"/>').append($link).appendTo($tabs);

    $link.on('click', function (event) {
        event.preventDefault();
        event.stopPropagation(); // Core's delegated handler only understands its own tabs.
        if ($files.children('.bs-media-actions').length) return;

        var $ns = $files.find('input[name="ns"]').first();
        var href = $tabs.find('a[href*="tab_files="]').first().prop('href');
        var ns = $ns.length ? $ns.val() : new URL(href, location.href).searchParams.get('ns') || '';
        // The template turns the current core tab's <strong> into href="#".
        // Give it a real URL so it can be selected again from Actions after AJAX navigation.
        $tabs.children().not('.bs-media-actions-tab').each(function (index) {
            var $tabLink = jQuery(this).children('a[href="#"]');
            if (!$tabLink.length) return;
            var url = new URL(href, location.href);
            url.searchParams.set('ns', ns);
            url.searchParams.set('tab_files', ['files', 'upload', 'search'][index]);
            $tabLink.attr('href', url.toString());
        });
        $tabs.children().removeClass('active');
        $link.parent().addClass('active');
        $files.children('.panelHeader, .panelContent').hide();
        var $header = jQuery('<div class="panelHeader bs-media-actions"/>').appendTo($files);
        jQuery('<h3/>').text(labels.mediaActionsIn + ' ')
            .append(jQuery('<strong/>').text(ns || labels.mediaRoot)).appendTo($header);
        var $panel = jQuery('<div class="panelContent bs-media-actions"/>').appendTo($files);
        var $status = jQuery('<p role="status"/>').text(labels.mediaLoading).appendTo($panel);
        var endpoint = DOKU_TPL + 'media-actions.php';
        function error(xhr) {
            var code = xhr.responseJSON && xhr.responseJSON.error;
            $status.text(labels['mediaError_' + code] || labels.mediaError_failed);
        }
        jQuery.getJSON(endpoint, {ns: ns}).done(function (data) {
            if (!$panel[0].isConnected) return;
            ns = data.ns;
            $header.find('strong').text(ns || labels.mediaRoot);
            $status.empty();
            if (data.canRemove) {
                var $remove = jQuery('<button type="button" class="btn btn-danger"/>')
                    .text(labels.mediaRemoveNamespace).prop('disabled', !data.empty).appendTo($panel);
                if (!data.empty) $remove.attr('title', labels.mediaError_notEmpty);
                $remove.on('click', function () {
                    var $dialog = jQuery('<div/>');
                    jQuery('<p/>').text(labels.mediaRemoveNamespaceConfirm).appendTo($dialog);
                    jQuery('<p/>').append(jQuery('<strong/>').text(ns)).appendTo($dialog);
                    var $confirm = jQuery('<button type="button" class="btn btn-danger"/>')
                        .text(labels.mediaRemove).appendTo($dialog);
                    var $feedback = jQuery('<p role="status"/>').appendTo($dialog);
                    var pending = false;
                    $dialog.dialog({
                        title: labels.mediaRemoveNamespace,
                        width: Math.min(600, window.innerWidth - 32),
                        appendTo: '.dokuwiki', modal: true,
                        beforeClose: function () { return !pending; },
                        close: function () { jQuery(this).dialog('destroy').remove(); }
                    });
                    $confirm.on('click', function () {
                        if (pending) return;
                        pending = true;
                        $confirm.prop('disabled', true);
                        $feedback.text(labels.mediaLoading);
                        jQuery.ajax({url: endpoint, method: 'POST', dataType: 'json', data: {
                            action: 'remove', ns: ns, sectok: data.sectok
                        }}).done(function (result) {
                            var url = new URL(location.href);
                            url.searchParams.set('ns', result.ns);
                            url.searchParams.set('tab_files', 'files');
                            url.searchParams.delete('image');
                            location.assign(url);
                        }).fail(function (xhr) {
                            var code = xhr.responseJSON && xhr.responseJSON.error;
                            $feedback.text(labels['mediaError_' + code] || labels.mediaError_failed);
                        }).always(function () { pending = false; $confirm.prop('disabled', false); });
                    });
                });
            }
            if (!data.canCreate) {
                $status.text(labels.mediaError_denied);
                return;
            }
            var $create = jQuery('<button type="button" class="btn btn-success"/>')
                .text(labels.mediaCreateNamespace).appendTo($panel);
            if (data.canRemove) $remove.insertAfter($create);
            $create.on('click', function () {
                var $dialog = jQuery('<div/>');
                jQuery('<p/>').text(labels.mediaCreateNamespaceIn + ' ')
                    .append(jQuery('<strong/>').text(ns || labels.mediaRoot)).appendTo($dialog);
                var $form = jQuery('<form class="bs-media-namespace-form"/>').appendTo($dialog);
                var $label = jQuery('<label/>').text(labels.mediaNamespaceName + ' ').appendTo($form);
                var $name = jQuery('<input type="text" class="edit form-control" required/>').appendTo($label);
                var $submit = jQuery('<button type="submit" class="btn btn-success"/>').text(labels.mediaCreate).appendTo($form);
                var $feedback = jQuery('<p role="status"/>').appendTo($form);
                var pending = false;
                $dialog.dialog({
                    title: labels.mediaCreateNamespace,
                    width: Math.min(600, window.innerWidth - 32),
                    appendTo: '.dokuwiki',
                    modal: true,
                    open: function () { $name.trigger('focus'); },
                    beforeClose: function () { return !pending; },
                    close: function () { jQuery(this).dialog('destroy').remove(); }
                });
                $form.on('submit', function (e) {
                    e.preventDefault();
                    if (pending) return;
                    pending = true;
                    $submit.prop('disabled', true);
                    $feedback.text(labels.mediaLoading);
                    jQuery.ajax({url: endpoint, method: 'POST', dataType: 'json', data: {
                        ns: ns, name: $name.val(), sectok: data.sectok
                    }}).done(function (result) {
                        // Refresh the tree as well as the files, and select the new namespace.
                        var url = new URL(location.href);
                        url.searchParams.set('ns', result.ns);
                        url.searchParams.set('tab_files', 'files');
                        url.searchParams.delete('image');
                        location.assign(url);
                    }).fail(function (xhr) {
                        var code = xhr.responseJSON && xhr.responseJSON.error;
                        $feedback.text(labels['mediaError_' + code] || labels.mediaError_failed);
                    }).always(function () {
                        pending = false;
                        $submit.prop('disabled', false);
                    });
                });
            });

            // Enabled plugins can register actions without coupling the template to their UI.
            var actions = [];
            jQuery(document).trigger('bootstrap3:media-actions', [actions, ns]);
            actions.forEach(function (action) {
                jQuery('<button type="button" class="btn btn-primary"/>').text(action.label)
                    .on('click', function () { action.run(ns); }).appendTo($panel);
            });
        }).fail(error);
    });
};
