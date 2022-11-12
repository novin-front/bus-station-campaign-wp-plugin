<?php
add_action( "admin_menu", "emigration_campaign_menu");

function emigration_campaign_menu (){
    add_menu_page(
        "مدیریت کمپین",
        'لیست اطلاعات',
        'manage_options',
        'emigration_campaign_dash',
        'emigration_campaign_plugin_handler',
    );
    add_submenu_page( 
        "emigration_campaign_dash",
         "خروجی گرفتن", 
         "خروجی گرفتن", 
         "manage_options", 
         "wp_create_user_manegement",
         "wp_emigration_campaign_plugin",
    );
}
function emigration_campaign_plugin_handler(){
    global $wpdb;
    $actions = $_GET["paged"];
    $paged=0;
    if($actions){
        $paged = intval($actions);
    }
    $list = $wpdb->get_results( "SELECT  COUNT(id) AS `total` FROM {$wpdb->prefix}table_campaign_data");
    $total = $list[0] -> total;
    $users_list = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}table_campaign_data LIMIT $paged, 10 ");
    include EM_PLUGIN_TPL .'admin/main.php';
}