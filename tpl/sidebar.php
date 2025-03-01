<?php
/**
 * DokuWiki Bootstrap3 Template: Sidebar
 *
 * @link     http://dokuwiki.org/template:bootstrap3
 * @author   Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

global $TPL;
global $ACT;

$sidebar_title = $lang['sidebar'];

if ($TPL->getConf('sidebarShowPageTitle')) {
    $sidebar_title = p_get_first_heading(page_findnearest($sidebar_page, $TPL->getConf('useACL')));
    if (! $sidebar_title) $sidebar_title = $lang['sidebar'];
}

?>
<!-- sidebar -->
<aside id="<?php echo $sidebar_id ?>" class="dw__sidebar <?php echo $sidebar_class ?> hidden-print">
    <div class="dw-sidebar-content">
        <div class="dw-sidebar-title hidden-lg hidden-md hidden-sm text-center" data-toggle="collapse" data-target="#<?php echo $sidebar_id ?> .panel-collapse-sidebar">
            <?php echo iconify('mdi:view-list'); ?> <?php echo $sidebar_title ?>
        </div>
        <div class="panel-collapse-sidebar collapse in">
        <div class="dw-sidebar-body small">
            <?php

                tpl_includeFile("$sidebar_header.html");
                if ($ACT == 'show') $TPL->includePage($sidebar_header);
                
                $TPL->normalizeSidebar($TPL->includePage($sidebar_page)); /* includes the nearest sidebar page */

                tpl_includeFile("$sidebar_footer.html");
                if ($ACT == 'show') $TPL->includePage($sidebar_footer)

            ?>
        </div>
        </div>
    </div>
</aside>
<!-- /sidebar -->
