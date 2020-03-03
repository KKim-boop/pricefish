<?php
/**
* Plugin Name: Woocommerce Open and Close
* Plugin URI: http://www.getjuicy.co.uk/plugins/woocommerce-minimum-order-limit
* Description: Open and close your store
* Version: 1.0 
* Author: getJuicy
* Author URI: http://getjuicy.co.uk
*/

//Don't call the file directly
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );


//OPen and Close
add_action ('init', 'checkOpenNew');

 
function checkOpenNew() {

$isOn = get_option("shopOn");

if ($isOn == 0){
   holiday_mode();


}

}

function holiday_mode() {
 remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
 remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
 remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
 remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
 add_action( 'woocommerce_before_main_content', 'getguicy_shop_disabled', 5 );
 add_action( 'woocommerce_before_cart', 'getguicy_shop_disabled', 5 );
 add_action( 'woocommerce_before_checkout_form', 'getguicy_shop_disabled', 5 );
}

function getguicy_shop_disabled() {
        wc_print_notice( 'Our Online Shop is Closed Currently. We open at 5:15pm :)', 'error');
}


   




//Hook into admin menu and run the function to add a new page
add_action('admin_menu', 'min_order_admin_actions');    

//Include the admin php file
function shop_open_and_close() {
    include('open_and_close_admin.php');
}


//Here is where we add the menu page and the menu item entry. The first option ‘Simple Order Notification’ is the title of our options page. The second parameter ‘Simple Order Notification’ is the label for our admin panel. The third parameter determines which users can see the option by limiting access to certain users with certain capabilities. ‘Simple Order Notification’ is the slug which is used to identify the menu. The final parameter ‘simple_order_notification_admin’ is the name of the function we want to call when the option is selected, this allows us to add code to output HTML to our page. In this case we just include the admin php file
function min_order_admin_actions() 
{
	add_menu_page(
        "Shop open and close",
        "Shop open and close",
        'edit_others_posts',
        "shop-open-and-close", 
        "shop_open_and_close"
        );

 
}
 




