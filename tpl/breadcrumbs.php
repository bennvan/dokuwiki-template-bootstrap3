<?php
/**
 * DokuWiki Bootstrap3 Template: Breadcrumbs
 *
 * @link     http://dokuwiki.org/template:bootstrap3
 * @author   Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

global $conf;
global $TPL;
global $ACT;
global $ID;
global $INFO;

$hidemodes = explode(' ', tpl_getConf('breadcrumbHideModes'));

// check control macro to render breadcrumbs
$meta = $INFO['meta']['internal'];
$nobread = (isset($meta['nobread']) ? $meta['nobread'] : false);

?>

<?php if ($conf['youarehere'] || $conf['breadcrumbs']): ?>


<?php if (!in_array($ACT, $hidemodes) && !$nobread): ?>
<!-- breadcrumbs -->
<nav id="dw__breadcrumbs" class="small">

    <hr/>

    <?php if($conf['youarehere']): ?>
    <div class="dw__youarehere">
        <?php $TPL->getYouAreHere()?>
    </div>
    <?php endif; ?>

    <?php if($conf['breadcrumbs']): ?>
    <div class="dw__breadcrumbs hidden-print">
        <?php $TPL->getBreadcrumbs() ?>
    </div>
    <?php endif; ?>

    <hr/>

</nav>

<?php endif ?>

<!-- /breadcrumbs -->
<?php endif ?>
