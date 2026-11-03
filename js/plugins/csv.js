/*
 * DokuWiki Bootstrap3 Template: Plugins Hacks!
 *
 * Home     http://dokuwiki.org/template:bootstrap3
 * Author   Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * License  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

// CSV Plugin

var $csv = jQuery('table tbody tr.row0 th.col0');

if ($csv.length) {

    $csv.each(function () {
        var $table = jQuery(this).closest('table');
        var $header = $table.children('tbody').children('tr.row0').first();
        // Only promote a complete legacy header row. A rowspan cannot cross
        // from thead into tbody, and mixed rows may contain side labels.
        if ($table.children('thead').length ||
            $header.children('td').length ||
            $header.children('th').filter(function () { return this.rowSpan !== 1; }).length) {
            return;
        }
        if ($table.children('tbody').children('tr.row1').children('th').length == 0) {
            jQuery('<thead/>').prependTo($table).append($header);
        }
    });

}
