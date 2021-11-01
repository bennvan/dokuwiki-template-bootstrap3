/*
 * DokuWiki Bootstrap3 Template: Plugins Hacks!
 *
 * Home     http://dokuwiki.org/template:bootstrap3
 * Author   Ben van magill <ben.vanmagill16@gmail.com>
 * License  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

// notfound plugin to show a create page button

(function() {
	if (!('ACT' in JSINFO)) return;
	if (JSINFO.ACT !== 'notfound') return;
	if (document.getElementById('dw__tools')){ 
		document.getElementById('notfound_create_page_btn').style.display='block'; 
		document.getElementById('dw__pagetools').remove(); 
	} 
})();