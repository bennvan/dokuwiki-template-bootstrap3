/*
 * DokuWiki Bootstrap3 Template: Plugins Hacks!
 *
 * Home     http://dokuwiki.org/template:bootstrap3
 * Author   Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * License  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

// Move Plugin

// The media button is inserted asynchronously by the plugin after a file is selected.
var $moveFilePanel = jQuery('#mediamanager__page .file').first();
if ($moveFilePanel.length && !$moveFilePanel.data('bootstrap3-move-styling')) {
    var styleMoveButton = function () {
        $moveFilePanel.find('button.move-btn').removeClass('btn-primary').addClass('btn btn-default').attr('type', 'button');
    };
    var moveButtonObserver = new MutationObserver(styleMoveButton);
    moveButtonObserver.observe($moveFilePanel[0], {childList: true, subtree: true});
    $moveFilePanel.data('bootstrap3-move-styling', moveButtonObserver);
    styleMoveButton();
}

// Enhance the existing form without replacing the move plugin's submit handler.
jQuery(document).on('dialogcreate.bootstrap3move', '.ui-dialog-content', function () {
    var $dialog = jQuery(this);
    // The page-tools rename dialog uses its own form and footer buttons.
    // New jQuery UI versions retain this legacy option but no longer add its class.
    if ($dialog.dialog('option', 'dialogClass') === 'plugin_move_dialog') {
        $dialog.addClass('bs-move-page-dialog');
        $dialog.dialog('option', {width: Math.min(600, window.innerWidth - 32), height: 'auto'});
        $dialog.find('input[name="id"]').addClass('form-control');
        $dialog.find('input[name="media"]').closest('label').addClass('bs-move-media-option');
        var $buttons = $dialog.dialog('widget').find('.ui-dialog-buttonpane button');
        $buttons.addClass('btn btn-default');
        $buttons.last().removeClass('btn-default').addClass('btn-primary');
        return;
    }
    var $destination = $dialog.find('input[name="move-new-filename"]');
    if (!$destination.length) return;

    $dialog.addClass('bs-move-media-dialog');
    $dialog.dialog('option', 'width', Math.min(600, window.innerWidth - 32));
    $destination.addClass('form-control').attr('id', 'bs-move-media-destination');
    var $fields = jQuery('<div class="bs-move-media-fields"/>').insertBefore($destination);
    jQuery('<label for="bs-move-media-destination"/>').text(LANG.plugins.move.newname).insertBefore($fields);
    $destination.appendTo($fields);
    $dialog.find('input[name="move-file-ext"]').addClass('form-control')
        .attr('aria-label', LANG.template.bootstrap3.mediaFileExtension).appendTo($fields);
    $dialog.find('form > button').addClass('btn btn-primary').attr('type', 'submit');
    $dialog.find('.move-error').addClass('alert alert-danger').attr('role', 'alert');
});

jQuery(document).on('bootstrap3:plugin-move', function (event) {

    if (!jQuery('#plugin_move__tree').length) return false;

    // console.debug(event.type + ' event fired');

    setTimeout(function () {

        var $directories = jQuery('li.type-d a.idx_dir'),
            $pages = jQuery('li.type-f a.wikilink1');

        jQuery.each($directories, function () {

            var $directory = jQuery(this),
                $closed = $directory.parents('.closed'),
                $open = $directory.parents('.open');

            if (!$directory.find('svg').length) {
                $directory.prepend(Iconify.getSVG('mdi:folder'));
            }

            if ($open.length) {
                $directory.find('svg').replaceWith(Iconify.getSVG('mdi:folder-open'));
            }

            if ($closed.length) {
                $directory.find('svg').replaceWith(Iconify.getSVG('mdi:folder'));
            }

            $directory.find('svg').addClass('iconify text-primary mr-2');

        });

        jQuery.each($pages, function () {

            var $page = jQuery(this);

            if (!$page.find('svg').length) {
                $page.prepend(Iconify.getSVG('mdi:file-document-outline'));
            }
            $page.find('svg').addClass('text-muted mr-2');

        });

    }, 0);

});

jQuery('#plugin_move__tree a').click(function (e) {
    dw_template.modeIndex();
});

jQuery(document).trigger('bootstrap3:plugin-move');

jQuery(document).ajaxSuccess(function (e) {
    jQuery(document).trigger('bootstrap3:plugin-move');
});
