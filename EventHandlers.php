<?php

namespace dokuwiki\template\bootstrap3;

/**
 * DokuWiki Bootstrap3 Template: Event Handlers Class
 *
 * @link     http://dokuwiki.org/template:bootstrap3
 * @author   Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @author   Ben van Magill <ben.vanmagill16@gmail.com>
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

use \dokuwiki\Form\HTMLElement;

class EventHandlers
{
    protected $template;

    public function __construct(Template $template)
    {
        $this->template = $template;

        /** @var \Doku_Event_Handler */
        global $EVENT_HANDLER;

        $events_dispatcher = [
            'FORM_QUICKSEARCH_OUTPUT'       => 'searchHandler',
            'FORM_SEARCH_OUTPUT'            => 'searchHandler',

            'FORM_SEARCHMEDIA_OUTPUT'       => ['BEFORE', ['commonStyles']],
            'FORM_UPLOAD_OUTPUT'            => ['BEFORE', ['commonStyles']],
            'FORM_CONFLICT_OUTPUT'          => ['BEFORE', ['commonStyles']],
            'FORM_DRAFT_OUTPUT'             => ['BEFORE', ['commonStyles', 'formDraftOutput']],
            'FORM_EDIT_OUTPUT'              => ['BEFORE', ['commonStyles', 'formEditOutput']],
            'FORM_LOGIN_OUTPUT'             => ['BEFORE', ['commonStyles', 'formAccountOutput']],
            'FORM_RESENDPWD_OUTPUT'         => ['BEFORE', ['commonStyles', 'formAccountOutput']],
            'FORM_UPDATEPROFILE_OUTPUT'     => ['BEFORE', ['commonStyles', 'formAccountOutput']],
            'FORM_PROFILEDELETE_OUTPUT'     => ['BEFORE', ['commonStyles', 'formAccountOutput']],
            'FORM_RECENT_OUTPUT'            => ['BEFORE', ['commonStyles', 'formRevisionsOutput']],
            'FORM_REGISTER_OUTPUT'          => ['BEFORE', ['commonStyles', 'formAccountOutput']],
            'FORM_REVISIONS_OUTPUT'         => ['BEFORE', ['commonStyles', 'formRevisionsOutput']],
            'FORM_SUBSCRIBE_OUTPUT'         => ['BEFORE', ['commonStyles', 'formAccountOutput']],

            /*-------------- Deprecated form events ------------*/
            'HTML_DRAFTFORM_OUTPUT'         => ['BEFORE',['htmlDraftFormOutput']],
            'HTML_EDITFORM_OUTPUT'          => ['BEFORE',['htmlEditFormOutput']],
            'HTML_LOGINFORM_OUTPUT'         => ['BEFORE',['htmlAccountFormOutput']],
            'HTML_RESENDPWDFORM_OUTPUT'     => ['BEFORE',['htmlAccountFormOutput']],
            'HTML_UPDATEPROFILEFORM_OUTPUT' => ['BEFORE',['htmlAccountFormOutput']],
            'HTML_PROFILEDELETEFORM_OUTPUT' => ['BEFORE',['htmlAccountFormOutput']],
            'HTML_RECENTFORM_OUTPUT'        => ['BEFORE',['htmlRevisionsFormOutput']],
            'HTML_REGISTERFORM_OUTPUT'      => ['BEFORE',['htmlAccountFormOutput']],
            'HTML_REVISIONSFORM_OUTPUT'     => ['BEFORE',['htmlRevisionsFormOutput']],
            'HTML_SUBSCRIBEFORM_OUTPUT'     => ['BEFORE',['htmlAccountFormOutput']],
            
            /*------------------------------------------*/

            'PLUGIN_TAG_LINK'               => ['BEFORE',['pluginTagLink']],
            'PLUGIN_TPLINC_LOCATIONS_SET'   => ['BEFORE',['tplIncPlugin']],
            'SEARCH_QUERY_FULLPAGE'         => ['BEFORE',['search']],
            'SEARCH_QUERY_PAGELOOKUP'       => ['BEFORE',['search']],
            'SEARCH_RESULT_FULLPAGE'        => ['BEFORE',['search']],
            'SEARCH_RESULT_PAGELOOKUP'      => ['BEFORE',['search']],
            'TPL_CONTENT_DISPLAY'           => ['BEFORE',['tplContent']],
            'TPL_METAHEADER_OUTPUT'         => ['BEFORE',['tplMetaHeaderOutput']],

        ];

        foreach ($events_dispatcher as $event => $data) {
            list($advise, $methods) = $data;
            foreach ($methods as $method) {
                $EVENT_HANDLER->register_hook($event, $advise, $this, $method);
            }
        }
    }

    public function formDraftOutput(\Doku_Event $event)
    {
        /** @var dokuwiki\Form\Form $form */
        $form = $event->data;

        $changes = [
            'do[recover]' => [
                'class' => 'btn-success',
                'icon'  => 'mdi:file-refresh',
                ],
            'do[draftdel]' => [
                'class' => 'btn-danger',
                'icon'  => 'mdi:delete',
                ],
            'do[show]' => [
                'class' => 'btn-default',
                'icon'  => 'mdi:arrow-left',
                ],
            ];

        foreach ($changes as $key => $set) {
            $pos = $form->findPositionByAttribute('name', $key);
            $elm = $form->getElementAt($pos);
            $elm->addClass($set['class']);
            $this->prependElmContent($form, $elm, $pos, iconify($set['icon']));
        }
    }

    public function formRevisionsOutput(\Doku_Event $event)
    {
        /** @var dokuwiki\Form\Form $form */
        $form = $event->data;

        for ($pos = 0; $pos < $form->elementCount(); $pos++) {

            $element = $form->getElementAt($pos);
            $type    = $element->getType();

            if ($type == 'tagopen') {
                if (strpos($element->attr('class'), 'minor') !== false) { 
                    $element->addClass('text-muted');
                }
            }

            if ($type == 'html') {
                $value = $element->val();
                $value = str_replace(['positive', 'negative'], ['positive label label-success', 'negative label label-danger'], $value);
                $element->val($value);
            }

        }
    }

    public function commonStyles(\Doku_Event $event)
    {
        /** @var dokuwiki\Form\Form $form */
        $form = $event->data;

        for ($pos = 0; $pos < $form->elementCount(); $pos++) {

            $element = $form->getElementAt($pos);
            $type    = $element->getType();

            if ($type == 'button') {
                $element->addClass('btn btn-default mr-2');
            }

        }
    }

    public function formAccountOutput(\Doku_Event $event)
    {
        /** @var dokuwiki\Form\Form $form */
        $form = $event->data;

        $title_icon   = 'account';
        $button_class = 'btn btn-success mr-2';
        $button_icon  = 'arrow-right';

        switch ($event->name) {
            case 'FORM_LOGIN_OUTPUT':
                $title_icon  = 'account';
                $button_icon = 'lock';
                break;
            case 'FORM_UPDATEPROFILE_OUTPUT':
                $title_icon = 'account-card-details-outline';
                break;
            case 'FORM_PROFILEDELETE_OUTPUT':
                $title_icon   = 'account-remove';
                $button_class = 'btn-danger';
                break;
            case 'FORM_REGISTER_OUTPUT':
                $title_icon = 'account-plus';
                break;
            case 'FORM_SUBSCRIBE_OUTPUT':
                $title_icon = null;
                break;
            case 'FORM_RESENDPWDFORM_OUTPUT':
                $title_icon = 'lock-reset';
                break;
        }

        // Save button
        $pos = $form->findPositionByAttribute('type', 'submit');
        if ($pos) {
            $elm = $form->getElementAt($pos)->addClass($button_class);
            $this->prependElmContent($form, $elm, $pos, iconify("mdi:$button_icon"));
        }

        // Legend
        $pos = $form->findPositionByType('fieldsetopen');
        if ($pos) {
            $elm = $form->getElementAt($pos);
            $this->prependElmContent($form, $elm, $pos, iconify("mdi:$title_icon"), 'legend');
        }

    }

    public function formEditOutput(\Doku_Event $event)
    {
        global $lang;

        /** @var dokuwiki\Form\Form $form */
        $form = $event->data;

        $changes = [
            'do[save]' => [
                'class' => 'btn-success',
                'icon'  => 'mdi:content-save',
                ],
            'do[preview]' => [
                'class' => 'btn-primary',
                'icon'  => 'mdi:file-document-outline',
                ],
            'do[changes]' => [
                'class' => 'btn-default',
                'icon'  => 'mdi:file-compare',
                ],
            'do[cancel]' => [
                'class' => 'btn-danger mr-2',
                'icon'  => 'mdi:arrow-left',
                ],
            ];

        foreach ($changes as $key => $set) {
            $pos = $form->findPositionByAttribute('name', $key);
            $elm = $form->getElementAt($pos);
            $elm->addClass($set['class']);
            $this->prependElmContent($form, $elm, $pos, iconify($set['icon']));
        }
    }

    public function prependElmContent($form, $elm, $pos, $prepend, $type='auto') {
        /** @var dokuwiki\Form\Form $form */
        /** @var dokuwiki\Form\Element $elm */
        if ($type === 'auto') $type = $elm->getType();

        $form->replaceElement(
            new HTMLElement(
                preg_replace('/(\<'.$type.'.*\>)(.*)(\<\/'.$type.'\>)/', '${1}'.$prepend.' ${2}${3}', $elm->toHTML())),
            $pos);
    }

    public function tplContent(\Doku_Event $event)
    {
        $event->data = $this->template->normalizeContent($event->data);
    }

    public function search(\Doku_Event $event)
    {
        if ($event->name == 'SEARCH_RESULT_PAGELOOKUP') {
            array_unshift($event->data['listItemContent'], iconify('mdi:file-document-outline', ['title' => hsc($event->data['page'])]) . ' ');
        }

        if ($event->name == 'SEARCH_RESULT_FULLPAGE') {
            $event->data['resultBody']['meta'] = str_replace(
                ['<span class="lastmod">', '<span class="hits">'],
                ['<span class="lastmod">' . iconify('mdi:calendar') . ' ', '<span class="hits">' . iconify('mdi:poll') . ' '],
                '<small>' . $event->data['resultBody']['meta'] . '</small>'
            );
        }
    }

    /**
     * Load the template assets (Bootstrap, AnchorJS, etc)
     *
     * @author  Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
     * @todo    Move the specific-padding size of Bootswatch template in template.less
     *
     * @param  \Doku_Event $event
     */
    public function tplMetaHeaderOutput(\Doku_Event $event)
    {

        global $ACT;
        global $INPUT;

        $fixed_top_navbar = $this->template->getConf('fixedTopNavbar');

        if ($google_analitycs = $this->template->getGoogleAnalitycs()) {
            $event->data['script'][] = [
                'type'  => 'text/javascript',
                '_data' => $google_analitycs,
            ];
        }

        // Apply some FIX
        if ($ACT || defined('DOKU_MEDIADETAIL')) {
            // Default Padding
            $navbar_padding = 20;

            if ($fixed_top_navbar) {
                $navbar_height = $this->template->getNavbarHeight();
                $navbar_padding += $navbar_height;
            }

            $styles = [];

            // TODO implement in css.php dispatcher

            $styles[] = "body { margin-top: {$navbar_padding}px; height: calc(100% - {$navbar_padding}px); }";
            $styles[] = ' #dw__toc.affix, #dw__toc-right.affix { top: ' . ($navbar_padding - 10) . 'px;}';

            if ($this->template->getConf('tocCollapseSubSections')) {
                $styles[] = ' #dw__toc .nav .nav .nav { display: none; }';
            }

            $event->data['style'][] = [
                'type'  => 'text/css',
                '_data' => '@media screen { ' . implode(" ", $styles) . ' }',
            ];
        }
    }

    public function pluginTagLink(\Doku_Event $event)
    {
        $event->data['class'] .= ' tag label label-default mx-1';
        $event->data['title'] = iconify('mdi:tag-text-outline') . ' ' . $event->data['title'];
    }

    public function tplIncPlugin(\Doku_Event $event)
    {
        $event->data['header']             = 'Header of page below the navbar (header)';
        $event->data['topheader']          = 'Top Header of page (topheader)';
        $event->data['pagefooter']         = 'Footer below the page content (pagefooter)';
        $event->data['pageheader']         = 'Header above the page content (pageheader)';
        $event->data['sidebarfooter']      = 'Footer below the sidebar (sidebarfooter)';
        $event->data['sidebarheader']      = 'Header above the sidebar (sidebarheader)';
        $event->data['rightsidebarfooter'] = 'Footer below the right-sidebar (rightsidebarfooter)';
        $event->data['rightsidebarheader'] = 'Header above the right-sidebar (rightsidebarheader)';
    }


// ------------ Deprecatet form handlers -------------

    /**
     * Handle various HTML_OUTPUT events
     * Deprecated in Igor release 
     * 
     * @param \Doku_Event $event Event handler
     *
     * @return void
     **/
    public function htmlAccountFormOutput(\Doku_Event $event)
    { 
        foreach ($event->data->_content as $key => $item) {
            if (is_array($item) && isset($item['_elem'])) {
                $title_icon   = 'account';
                $button_class = 'btn btn-success';
                $button_icon  = 'arrow-right';

                switch ($event->name) {
                    case 'HTML_LOGINFORM_OUTPUT':
                        $title_icon  = 'account';
                        $button_icon = 'lock';
                        break;
                    case 'HTML_UPDATEPROFILEFORM_OUTPUT':
                        $title_icon = 'account-card-details-outline';
                        break;
                    case 'HTML_PROFILEDELETEFORM_OUTPUT':
                        $title_icon   = 'account-remove';
                        $button_class = 'btn btn-danger';
                        break;
                    case 'HTML_REGISTERFORM_OUTPUT':
                        $title_icon = 'account-plus';
                        break;
                    case 'HTML_SUBSCRIBEFORM_OUTPUT':
                        $title_icon = null;
                        break;
                    case 'HTML_RESENDPWDFORM_OUTPUT':
                        $title_icon = 'lock-reset';
                        break;
                }

                // Legend
                if ($item['_elem'] == 'openfieldset') {
                    $event->data->_content[$key]['_legend'] = (($title_icon) ? iconify("mdi:$title_icon") : '') . ' ' . $event->data->_content[$key]['_legend'];
                }

                // Save button
                if (isset($item['type']) && $item['type'] == 'submit') {
                    $event->data->_content[$key]['class'] = " $button_class";
                    $event->data->_content[$key]['value'] = (($button_icon) ? iconify("mdi:$button_icon") : '') . ' ' . $event->data->_content[$key]['value'];
                }
            }
        }
    }

    /**
     * Handle HTML_DRAFTFORM_OUTPUT event
     * Deprecated in Igor release 
     * 
     * @param \Doku_Event $event Event handler
     *
     * @return void
     **/
    public function htmlDraftFormOutput(\Doku_Event $event)
    {
        foreach ($event->data->_content as $key => $item) {
            if (is_array($item) && isset($item['_elem'])) {
                if ($item['_action'] == 'draftdel') {
                    $event->data->_content[$key]['class'] = ' btn btn-danger';
                    $event->data->_content[$key]['value'] = iconify('mdi:close') . ' ' . $event->data->_content[$key]['value'];
                }

                if ($item['_action'] == 'recover') {
                    $event->data->_content[$key]['value'] = iconify('mdi:refresh') . ' ' . $event->data->_content[$key]['value'];
                }

                if ($item['_action'] == 'show') {
                    $event->data->_content[$key]['value'] = iconify('mdi:arrow-left') . ' ' . $event->data->_content[$key]['value'];
                }
            }
        }
    }

    /**
     * Handle HTML_EDITFORM_OUTPUT and HTML_DRAFTFORM_OUTPUT event
     * Deprecated in Igor release
     *
     * @param \Doku_Event $event Event handler
     *
     * @return void
     **/
    public function htmlEditFormOutput(\Doku_Event $event)
    {
        foreach ($event->data->_content as $key => $item) {
            if (is_array($item) && isset($item['_elem'])) {
                // Save button
                if ($item['_action'] == 'save') {
                    $event->data->_content[$key]['class'] = ' btn btn-success';
                    $event->data->_content[$key]['value'] = iconify('mdi:content-save') . ' ' . $event->data->_content[$key]['value'];
                }

                // Preview and Show buttons
                if ($item['_action'] == 'preview' || $item['_action'] == 'show') {
                    $event->data->_content[$key]['class'] = ' btn btn-primary';
                    $event->data->_content[$key]['value'] = iconify('mdi:file-document-outline') . ' ' . $event->data->_content[$key]['value'];
                }

                // Changes button (requres changes plugin)
                if ($item['_action'] == 'changes') {
                    $event->data->_content[$key]['value'] = iconify('mdi:file-compare') . ' ' . $event->data->_content[$key]['value'];
                }

                // Cancel button
                if ($item['_action'] == 'cancel') {
                    $event->data->_content[$key]['class'] = ' btn btn-danger';
                    $event->data->_content[$key]['value'] = iconify('mdi:arrow-left') . ' ' . $event->data->_content[$key]['value'];
                }
            }
        }
    }

    /**
     * Handle HTML_REVISIONSFORM_OUTPUT and HTML_RECENTFORM_OUTPUT events
     * Deprecated in Igor release
     *
     * @param \Doku_Event $event Event handler
     *
     * @return void
     **/
    public function htmlRevisionsFormOutput(\Doku_Event $event)
    {
        foreach ($event->data->_content as $key => $item) {
            // Revision form
            if (is_array($item) && isset($item['_elem'])) {
                if ($item['_elem'] == 'opentag' && $item['_tag'] == 'span' && strstr($item['class'], 'sizechange')) {
                    if (strstr($item['class'], 'positive')) {
                        $event->data->_content[$key]['class'] .= ' label label-success';
                    }

                    if (strstr($item['class'], 'negative')) {
                        $event->data->_content[$key]['class'] .= ' label label-danger';
                    }
                }

                // Recent form
                if ($item['_elem'] == 'opentag' && $item['_tag'] == 'li' && strstr($item['class'], 'minor')) {
                    $event->data->_content[$key]['class'] .= ' text-muted';
                }
            }
        }
    }
}