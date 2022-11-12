<?php
/*
Plugin Name: ذخیره اطلاعات کمپین مهاجرتی
Plugin URI:  https://myazdanpanah.com/
Description: پلاگین برای ذخیره اطلاعات کمپین تبلغاتی 
Version:     1.0
Author:      mohammad yazdanpanah
Author URI: https://myazdanpanah.com/
*/

define('EM_PLUGIN_DIR',plugin_dir_path( __FILE__ ));
define('EM_PLUGIN_URL',plugin_dir_url( __FILE__ ));
define('EM_PLUGIN_INC',EM_PLUGIN_DIR.'/inc/' );
define('EM_PLUGIN_TPL',EM_PLUGIN_DIR . '/tpl/' );
define('PANEL_USER_NAME','275356' );
define('PANEL_PASSWORD', '4ac9c774aba65295b02966d2f8aa60e47163386f' );

include EM_PLUGIN_INC."functions.php";





function wp_emigration_activation_plugin_func(){
    wp_emigration_set_default_plugin_config();
    wp_emigration_create_database_table();
    wp_referring_users_create_database_table();
}
function wp_emigration_deactivation_plugin_func() {
}

register_deactivation_hook( __FILE__, 'wp_emigration_deactivation_plugin_func' );
register_activation_hook( __FILE__, 'wp_emigration_activation_plugin_func' );



if(is_admin()){
    include EM_PLUGIN_INC .'admin/menu.php';
}
