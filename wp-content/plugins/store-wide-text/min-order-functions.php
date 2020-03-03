<?php
/**
* Plugin Name: STore Wide Text
* Plugin URI: http://www.getjuicy.co.uk/plugins/store-wide-text
* Description: Set store wide text
* Version: 1.0 
* Author: getJuicy
* Author URI: http://getjuicy.co.uk
*/

//Don't call the file directly
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );





//Hook into admin menu and run the function to add a new page
add_action('admin_menu', 'store_wide_admin_actions');    

//Include the admin php file
function store_wide_text() {
    include('min_order_admin.php');
}


//Here is where we add the menu page and the menu item entry. The first option ‘Simple Order Notification’ is the title of our options page. The second parameter ‘Simple Order Notification’ is the label for our admin panel. The third parameter determines which users can see the option by limiting access to certain users with certain capabilities. ‘Simple Order Notification’ is the slug which is used to identify the menu. The final parameter ‘simple_order_notification_admin’ is the name of the function we want to call when the option is selected, this allows us to add code to output HTML to our page. In this case we just include the admin php file
function store_wide_admin_actions() 
{
	add_menu_page(
        "Store wide text",
        "Store wide text",
        'edit_others_posts', 
        "store-wide-text", 
        "store_wide_text"
        );

 
}
 




