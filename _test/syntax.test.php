<?php
/**
 * General tests for the tabbox plugin
 *
 * @group plugin_tabbox
 * @group plugins
 */
class syntax_plugin_tabbox_test extends DokuWikiTest {

    protected $pluginsEnabled = array('tabbox');


    /**
     * Simple test to make sure the plugin.info.txt is in correct format
     */
    public function test_instructions() {


        $input  = '<tabbox box1>'.NL;
        $input .= 'content1'.NL;
        $input .= '<tabbox box2>'.NL;
        $input .= 'content2'.NL;
        $input .= '</tabbox>'.NL;

        print_r(p_get_instructions($input));


    }
}
