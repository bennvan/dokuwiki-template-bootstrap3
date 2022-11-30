<?php

/**
 * DokuWiki Bootstrap3 Template: User Menu
 *
 * @link     http://dokuwiki.org/template:bootstrap3
 * @author   Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

global $INFO, $lang, $TPL;

$user = $INPUT->server->str('REMOTE_USER');
$groups = $INFO['userinfo']['grps'];
$use_avatar = $TPL->getConf('useAvatar');
$is_guest = auth_isMember('@guest',$user,$INFO['userinfo']['grps']);
$is_user = auth_isMember('@user',$user,$INFO['userinfo']['grps']);

if ($TPL->getPlugin('bootswrapper') && $is_user) {
    $hlp = $TPL->getPlugin('bootswrapper');
    $readonly = $hlp->get_user_settings('render readonly');
} else {
    $readonly = false;
}

$extensions_update = array();
$avatar_size       = 96;
$avatar_size_small = 32;

if ($use_avatar && !$is_guest) {
    $avatar_img_small = $TPL->getAvatar($user, $INFO['userinfo']['mail'], $avatar_size_small);
    $avatar_img       = $TPL->getAvatar($user, $INFO['userinfo']['mail'], $avatar_size);
} else {
    $avatar_img = tpl_getMediaFile(array('images/avatar.png'));
    $avatar_img_small = $avatar_img;
}

$label_type = 'info';
$user_type  = 'User';

if ($INFO['ismanager']) {
    $label_type = 'warning';
    $user_type  = 'Manager';
}

if ($INFO['isadmin']) {
    $label_type = 'danger';
    $user_type  = 'Admin';
}

if ($is_user && $readonly){
    $label_type = 'info';
    $user_type = 'Read-only mode';
}

if ($INFO['isadmin'] && $TPL->getConf('notifyExtensionsUpdate')) {

    /** @var $plugin_controller PluginController */
    global $plugin_controller;

    $extension = plugin_load('helper','extension_extension');

    foreach ($plugin_controller->getList('', true) as $plugin) {
        $extension->setExtension($plugin);
        if ($extension->updateAvailable() && $extension->isEnabled()) {
            $extensions_update[] = $extension->getDisplayName();
        }
    }

  sort($extensions_update);

}

?>
<!-- user-menu -->
<ul class="nav navbar-nav" id="dw__user_menu">
    <li class="dropdown">

        <a href="<?php wl($ID) ?>" class="dropdown-toggle" data-target="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <?php if ($use_avatar): ?>
            <img alt="<?php echo hsc($user) ?>" src="<?php echo $avatar_img_small ?>" class="img-circle profile-image" width="<?php echo $avatar_size_small ?>" height="<?php echo $avatar_size_small ?>" />
            <?php else: ?>
            <?php echo iconify('mdi:account'); ?>
            <?php endif; ?> <span class="hidden-lg hidden-md hidden-sm"><?php echo hsc($user) ?></span> <span class="caret"></span>
        </a>

        <ul class="dropdown-menu" role="menu">

            <li>

                <div class="container-fluid">

                    <p class="text-right">
                        <span style="cursor:help" class="label label-<?php echo $label_type; ?>" title="<?php echo tpl_getLang('user_groups'); ?>: <?php echo join(', ', $groups); ?>">
                            <?php echo $user_type; ?>
                        </span>
                    </p>

                    <p class="text-center">
                        <img alt="<?php echo hsc($user) ?>" src="<?php echo $avatar_img ?>" class="img-circle" width="<?php echo $avatar_size ?>" height="<?php echo $avatar_size ?>" />
                    </p>

                    <div class="mb-2">
                        <div class="mb-2">
                            <strong><?php echo hsc($INFO['userinfo']['name']) ?></strong>
                        </div>
                        <div class="small">
                            <bdi><?php echo hsc($user) ?></bdi>
                        </div>
                        <div class="small">
                            <?php echo $INFO['userinfo']['mail'] ?>
                        </div>
                    </div>

                </div>

            </li>

            <li class="divider"></li>

            <?php if ($TPL->getConf('showUserHomeLink') && in_array('user', $INFO['userinfo']['grps'])): ?>
            <li class="dropdown-header"><?php echo tpl_getLang('userpages') ?></li>
            <?php

                echo '<li><a rel="nofollow" href="' . $TPL->getUserHomePageLink() . '" title="'. tpl_getLang('privatenamespace_desc') .'">' .
                     iconify('mdi:lock') . ' ' . tpl_getLang('privatenamespace') .'</a></li>';

                echo '<li><a rel="nofollow" href="' . $TPL->getUserSharePageLink() . '" title="'. tpl_getLang('sharednamespace_desc') .'">' .
                     iconify('mdi:account-network') . ' ' . tpl_getLang('sharednamespace') .'</a></li>';

                echo '<li><a rel="nofollow" href="' . $TPL->getUserPublicPageLink() . '" title="'. tpl_getLang('publicnamespace_desc') .'">' .
                     iconify('mdi:earth') . ' ' . tpl_getLang('publicnamespace') .'</a></li>';

            ?>

            <li class="divider"></li>
            <?php endif; ?>

            <li class="dropdown-header"><?php echo $lang['user_tools'] ?></li>

            <?php

                echo $TPL->getToolMenuItemLink('user', 'profile');

                if ($INFO['isadmin']) {
                    echo $TPL->getToolMenuItemLink('user', 'admin');
                }

                if ($TPL->getPlugin('bootswrapper') && $is_user) {
                    $cls = $readonly ? 'active' : '';
                    $html = '<li class="' . $cls . '">';
                    $html .= '<a rel="nofollow" href="' . wl($ID,['do'=>'readonly']) . '" title="'. tpl_getLang('readonly_desc') .'">';
                    $html .= iconify('mdi:file-lock');
                    $html .= '<span>' . ' ' . hsc(tpl_getLang('readonly')) . '</span>';
                    $html .= '</a>';

                    echo $html;
                } 

            ?>

            <?php if ($INFO['isadmin'] && count($extensions_update)): ?>
            <li>
                <a href="<?php echo wl($ID, array('do' => 'admin', 'page' => 'extension')); ?>" title=" - <?php echo implode('&#13; - ', $extensions_update) ?>">
                    <?php echo iconify('mdi:puzzle', array('class' => 'text-success')) ?> <?php echo tpl_getLang('extensions_update'); ?> <span class="badge"><?php echo count($extensions_update) ?></span>
                </a>
            </li>
            <?php endif; ?>

            <li class="divider"></li>

            <?php

                // Add the user menu

                $usermenu_pageid = null;
                $user_homepage_id = $TPL->getUserHomePageID();

                foreach (array("$user_homepage_id:usermenu", 'usermenu') as $id) {
                    $usermenu_pageid = page_findnearest($id, $TPL->getConf('useACL'));
                    if ($usermenu_pageid) break;
                }

                if ($usermenu_pageid) {

                    $html = new simple_html_dom;
                    $html->load($TPL->includePage($usermenu_pageid, true), true, false);

                    foreach ($html->find('h1,h2,h3,h4,h5,h6') as $elm) {
                        $elm->outertext = '<li class="dropdown-header">' . $elm->innertext . '</li>';
                    }
                    foreach ($html->find('hr') as $elm) {
                        $elm->outertext = '<li class="divider"></li>';
                    }
                    foreach ($html->find('ul') as $elm) {
                        $elm->outertext = '' . $elm->innertext;
                    }
                    foreach ($html->find('div') as $elm) {
                        $elm->outertext = $elm->innertext;
                    }

                    $content = $html->save();

                    $html->clear();
                    unset($html);

                    $content = str_replace('urlextern', '', $content);

                    echo $content;
                    echo '<li class="divider"></li>';

                }
            ?>

            <?php
                echo $TPL->getToolMenuItemLink('user', 'logout');
            ?>

        </ul>
    </li>
</ul>
<!-- /user-menu -->
