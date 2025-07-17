<?php
/**
 * TEQcidbPlugin Admin Menu Class
 *
 * @author   Jake Evans
 * @category Admin
 * @package  Includes/UI/Admin
 * @version  1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'TEQcidbPlugin_Admin_Menu', false ) ) :
/**
 * TEQcidbPlugin_Admin_Menu Class.
 */
class TEQcidbPlugin_Admin_Menu {


    public function __construct() {

        // Get active plugins to see if any extensions are in play
        $this->active_plugins = (array) get_option('active_plugins', array());
        if (is_multisite()) {
            // On the one multisite I have troubleshot, all plugins were merged into the $this->active_plugins variable, but the multisite plugins had an int value, not the actual name of the plugin, so, I had to build an array composed of the keys of the array that get_site_option('active_sitewide_plugins', array()) returned, and merge that.
            $multi_plugin_actual_name = array();
            $temp = get_site_option('active_sitewide_plugins', array());
            foreach ($temp as $key => $value) {
                array_push($multi_plugin_actual_name, $key);
            }

            $this->active_plugins = array_merge($this->active_plugins, $multi_plugin_actual_name);
        }

        // Get current menu/submenu 
        if(!empty($_GET['page'])){
            $this->page = filter_var($_GET['page'], FILTER_SANITIZE_STRING);
        }

        // Get current tab - if no tab, set $this->activetab to default
        if(!empty($_GET['tab'])){
            $this->activetab = filter_var($_GET['tab'], FILTER_SANITIZE_STRING);
        } else {
            $this->activetab = 'default';
        }

        // Controls UI for each Menu/Submenu page
        switch ( $this->page ) {
            case 'TEQciDb-Options-students':
                $this->setup_settings_ui();
                break; 
            case 'TEQciDb-Options-classes':
                $this->setup_submenupage1_ui();
                break;  
             case 'TEQciDb-Options-emails':
                $this->setup_submenupage2_ui();
                break;
             case 'TEQciDb-Options-reports':
                $this->setup_submenupage3_ui();
                break;         
            default:
                // Controls UI for submenu pages added through extensions
                $this->setup_dynamic_ui();
                break;
        }
    }

    // Sets up tabs for the 'Books' page
    private function setup_settings_ui() {
        $this->tabs = array(
            'students1'   => __("Add a New Student", 'teqcidbplugin'),
            'students2'  => __("Edit Existing Students", 'teqcidbplugin'),
            'students3'  => __("Student Forms", 'teqcidbplugin'),
        );

        if(has_filter('teqcidb_add_tab_settings')) {
            $this->tabs = apply_filters('teqcidb_add_tab_settings', $this->tabs);
        }

        if($this->activetab == 'default'){
            $this->activetab = null;
        }

        $this->output_tabs_ui();
        $this->output_indiv_tab();
    }

     // Sets up tabs for the 'Books' page
    private function setup_submenupage1_ui() {
        $this->tabs = array(
            'classes1'   => __("Create a New Class", 'teqcidbplugin'),
            'classes2'  => __("Edit Existing Classes", 'teqcidbplugin'),
            'classes3'  => __("Class Forms", 'teqcidbplugin'),
        );

        if(has_filter('teqcidb_add_tab_submenupage1')) {
            $this->tabs = apply_filters('teqcidb_add_tab_submenupage1', $this->tabs);
        }

        if($this->activetab == 'default'){
            $this->activetab = null;
        }

        $this->output_tabs_ui();
        $this->output_indiv_tab();
    }

    // Sets up tabs for the 'Books' page
    private function setup_submenupage2_ui() {
        $this->tabs = array(
            'emails1'   => __("Create an Email Template", 'teqcidbplugin'),
            'emails2'  => __("Edit & Delete Email Templates", 'teqcidbplugin'),
            'emails3'  => __("Send Emails", 'teqcidbplugin'),
        );

        if(has_filter('teqcidb_add_tab_submenupage2')) {
            $this->tabs = apply_filters('teqcidb_add_tab_submenupage2', $this->tabs);
        }

        if($this->activetab == 'default'){
            $this->activetab = null;
        }

        $this->output_tabs_ui();
        $this->output_indiv_tab();
    }

    // Sets up tabs for the 'Books' page
    private function setup_submenupage3_ui() {
        $this->tabs = array(
            'reports1'   => __("QCI Numbers", 'teqcidbplugin'),
            'reports2'  => __("Credits List", 'teqcidbplugin'),
            'reports3'  => __("Represented States", 'teqcidbplugin'),
            'reports4'  => __("New Students", 'teqcidbplugin'),
            'reports5'  => __("New Class Payments", 'teqcidbplugin'),
        );

        if(has_filter('teqcidb_add_tab_submenupage3')) {
            $this->tabs = apply_filters('teqcidb_add_tab_submenupage3', $this->tabs);
        }

        if($this->activetab == 'default'){
            $this->activetab = null;
        }

        $this->output_tabs_ui();
        $this->output_indiv_tab();
    }

    // Sets up the tabs for a submenu page that has been added by an extension
    private function setup_dynamic_ui() {
        $path = $this->build_extension_path();
        $path = $path.'/includes/ui/';
        $dir_array = scandir($path);
        $page = explode('-',$this->page);
        $tab_array = array();
        $tab_display_array = array();
        $tab_slug_array = array();

        foreach($dir_array as $file){
            if($file == '.' || $file == '..'){
                continue;
            }

            if($file == 'teqcidbplugin-'.$page[2].'.php'){
                continue;
            }

            $filestring = explode('-', $file);
            foreach($filestring as $string){
                if($string == 'admin' || $string == 'class' || $string == 'tab' || $string == 'extension' || $string == 'ui.php'){
                    continue;
                } else{
                    array_push($tab_array, $string);
                }
            }

            array_shift($tab_array);
            $final_tab_string = '';
            $final_tab_string_for_display = '';
            foreach($tab_array as $tabpart){
                $final_tab_string_for_display = $final_tab_string_for_display.' '.ucfirst($tabpart);
                $final_tab_string = $final_tab_string.'-'.$tabpart;
            }

            array_push($tab_display_array, ltrim($final_tab_string_for_display, ' '));
            array_push($tab_slug_array, ltrim($final_tab_string, '-'));

            $final_tab_string_for_display = '';
            $final_tab_string = '';
            $tab_array = array();
        }

        $this->tabs = array();
        foreach($tab_slug_array as $key=>$slug){
            $this->tabs[$slug] = __($tab_display_array[$key], 'teqcidbplugin');
        }

        // A filter to add tabs to the submenu page. So the submenu extensions can have their own separate plugins that add tabs to it. The name of this filter will be 'teqcidb_add_tab_' plus the one-word unique identifer for this extension, i.e., the word that is displayed in the TEQcidbPlugin plugin main menu.  
        if(has_filter('teqcidb_add_tab_'.$page[2])) {
            $this->tabs = apply_filters('teqcidb_add_tab_'.$page[2], $this->tabs);
        }

        //if($this->tabs[0] == ''){
            //array_shift($this->tabs);
        //}

        if($this->activetab == 'default'){
            $this->activetab = null;
        }

        $this->output_tabs_ui();
        $this->output_indiv_tab();
    }

    // The function that actually generates the tabs on a page
    private function output_tabs_ui() {
        $current = '';
        if(!empty($_GET['tab'])){
            $this->activetab = filter_var($_GET['tab'], FILTER_SANITIZE_STRING);
        } else {
            reset($this->tabs);
            $this->activetab = strtolower(key($this->tabs));
        }

        $html =  '<h2 class="nav-tab-wrapper">';
        foreach( $this->tabs as $tab => $name ){
            $class = ($tab == $current) ? 'nav-tab-active' : '';
            $html .=  '<a class="nav-tab ' . $class . '" href="?page='.$this->page.'&tab=' . $tab . '">' . $name . '</a>';
        }
        $html .= '</h2>';
        echo $html;
    }

    // The function that controls the output for each individual tab
    private function output_indiv_tab() {
        $this->activetab;
        $this->page;
        $page = explode('-', $this->page);
        $filename = 'class-admin-'.$page[2].'-'.$this->activetab.'-tab-ui.php';

        // Support for Extensions
        if(!file_exists(TEQCIDB_ROOT_INCLUDES_UI_ADMIN_DIR.$filename)){
            error_log('$path222');
                error_log($filename);
            $path = $this->build_extension_path();
            if(is_dir($path)){
                error_log('$path2224444');
                $path = $path.'/includes/ui/class-admin-'.$page[2].'-'.$this->activetab.'-tab-extension-ui.php';
                require_once($path);
            } else {
                error_log('$path');
                error_log($path);
                require_once($path);
            }
        } else {
            // Look for file in core plugin
           require_once(TEQCIDB_ROOT_INCLUDES_UI_ADMIN_DIR.$filename);
        }
    }

    // The function that builds paths for extensions, both for creating a new submenu page, and tabs that have been added via extensions.
    private function build_extension_path() {
        $page = explode('-', $this->page);
        foreach($this->active_plugins as $plugin){
            if(strpos($plugin, 'teqcidbplugin-') !== false){
                if(strpos($plugin, $this->activetab) !== false){
                    $temp = explode('-', $plugin);
                    if($temp[2] === $this->activetab.'.php'){
                        $filename = 'class-admin-'.$page[2].'-'.$this->activetab.'-tab-extension-ui.php';
                        $path = ROOT_WP_PLUGINS_DIR.$temp[0].'-'.$this->activetab.'/'.$filename;
                    } else {
                        echo 'something wrong';
                    }
                }


                
                if(!isset($path)){
                    $path = null;
                }

                if(file_exists($path) && !is_dir($path)){
                    return $path;
                } else {
                    $page = explode('-', $this->page);
                    if(strpos($plugin, $page[2]) !== false){
                        $path = ROOT_WP_PLUGINS_DIR.'teqcidbplugin-'.$page[2];
                        if(file_exists($path)){
                            return $path;
                        }
                    }
                }
            }
        }
    }

}
endif;


// Instantiate the class
$am = new TEQcidbPlugin_Admin_Menu;