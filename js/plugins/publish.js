/*
 * DokuWiki Bootstrap3 Template: Plugins Hacks!
 *
 * Home     http://dokuwiki.org/template:bootstrap3
 * Author   Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * License  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

// Publish Plugin

var $approvals = jQuery('.apr_table');

if ($approvals.length) {

    $approvals.removeClass('table-striped');

}

// $publish.prependTo('.page');