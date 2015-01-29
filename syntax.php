<?php
/**
 * DokuWiki Plugin tabbox (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

class syntax_plugin_tabbox extends DokuWiki_Syntax_Plugin {

    protected $tabs = array();
    protected $tabids = array();
    protected $intab = false;
    protected $original_doc = '';

    /**
     * @return string Syntax mode type
     */
    public function getType() {
        return 'formatting';
    }
    /**
     * @return string Paragraph type
     */
    public function getPType() {
        return 'stack';
    }
    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort() {
        return 190;
    }

    /**
     * @return array Things that may be inside the syntax
     */
    function getAllowedTypes() {
        return array('container', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs');
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
        $this->Lexer->addEntryPattern('<tabbox.*?>(?=.*?</tabbox>)',$mode,'plugin_tabbox');
    }

    public function postConnect() {
        $this->Lexer->addExitPattern('</tabbox>', 'plugin_tabbox');
    }

    /**
     * Handle matches of the tabbox syntax
     *
     * @param string $match The match of the syntax
     * @param int    $state The state of the handler
     * @param int    $pos The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler &$handler){

        if($state == DOKU_LEXER_UNMATCHED && substr($match, 0, 7) == '<tabbox'){
            $state = DOKU_LEXER_ENTER;
        }

        return array($state, $match);
    }



    /**
     * Render xhtml output or metadata
     *
     * @param string         $mode      Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer  $renderer  The renderer
     * @param array          $data      The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer &$renderer, $data) {
        if($mode != 'xhtml') return false;

        list($state, $match) = $data;

        switch ($state) {
            case DOKU_LEXER_ENTER:
                if(!$this->intab) {
                    // this is the first tab
                    $this->_openBox($renderer);
                } else {
                    // close last tab
                    $this->_closeTab($renderer);
                }
                // open new tab
                $this->_openTab($renderer, substr($match, 7, -1));
                break;
            case DOKU_LEXER_EXIT:
                if($this->intab) {
                    // close last tab
                    $this->_closeTab($renderer);
                }
                $this->_closeBox($renderer);
                break;
            default:
                // just render as is
                $renderer->cdata($match);
        }

        return true;
    }


    protected function _fixParagraphs($string) {
        $string = preg_replace('/^\\s*<\/p>/', '', $string);
        $string = preg_replace('/<p>\\s*$/', '', $string);
        return $string;
    }

    /**
     * Open a new tab with the given name
     *
     * @param Doku_Renderer_xhtml $R
     * @param string $name
     */
    protected function _openTab(Doku_Renderer_xhtml $R, $name) {
        $name = trim($name);
        $next = count($this->tabs);
        $this->tabs[$next]['id'] =  sectionID('tab__'.$name, $this->tabids);
        $this->tabs[$next]['name'] = $name;
        $this->tabs[$next]['data'] = '';

        $R->doc =& $this->tabs[$next]['data']; // write content to this tab now

        $this->intab = true;
    }

    /**
     * Close the current tab
     *
     * @param Doku_Renderer_xhtml $R
     */
    protected function _closeTab(Doku_Renderer_xhtml $R) {
        $cur = count($this->tabs) - 1;
        $this->tabs[$cur]['data'] = $this->_fixParagraphs($this->tabs[$cur]['data']);
        $this->intab = false;

        $R->doc =& $this->original_doc; // restore original doc
    }

    /**
     * Open a new tab box
     *
     * @param Doku_Renderer_xhtml $R
     */
    protected function _openBox(Doku_Renderer_xhtml $R) {
        $this->tabs = array();
        $this->tabids = array();
        $this->original_doc =& $R->doc; // save current content
    }

    /**
     * Close the tab box and write the tabs to the output
     *
     * @param Doku_Renderer_xhtml $R
     */
    protected function _closeBox(Doku_Renderer_xhtml $R) {
        $R->doc .= '<div class="plugin_tabbox">';

        $R->doc .= '<ul class="tabs">';
        foreach($this->tabs as $tab) {
            $R->doc .= '<li>';
            $R->doc .= '<a href="#'.$tab['id'].'">'.hsc($tab['name']).'</a>';
            $R->doc .= '</li>';
        }
        $R->doc .= '</ul>';

        foreach($this->tabs as $tab) {
            $R->doc .= '<div class="tab" id="'.$tab['id'].'">';
            $R->doc .= $tab['data'];
            $R->doc .= '</div>';
        }

        $R->doc .= '</div>';
    }


}

// vim:ts=4:sw=4:et:
