<?php
/**
 * Pizzaro engine room
 *
 * @package pizzaro
 */

/**
 * Assign the Pizzaro version to a var
 */
$theme              = wp_get_theme( 'pizzaro' );
$pizzaro_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 980; /* pixels */
}

/**
 * Initialize all the things.
 */
require get_template_directory() . '/inc/class-pizzaro.php';

require get_template_directory() . '/inc/pizzaro-functions.php';
require get_template_directory() . '/inc/pizzaro-template-hooks.php';
require get_template_directory() . '/inc/pizzaro-template-functions.php';

/**
 * Redux Framework
 * Load theme options and their override filters
 */
if ( is_redux_activated() ) {
	require get_template_directory() . '/inc/redux-framework/pizzaro-options.php';
	require get_template_directory() . '/inc/redux-framework/hooks.php';
	require get_template_directory() . '/inc/redux-framework/functions.php';
}

if( is_jetpack_activated() ) {
	require get_template_directory() . '/inc/jetpack/class-pizzaro-jetpack.php';
}

if ( is_woocommerce_activated() ) {
	require get_template_directory() . '/inc/woocommerce/class-pizzaro-woocommerce.php';
	require get_template_directory() . '/inc/woocommerce/class-pizzaro-shortcode-products.php';
	require get_template_directory() . '/inc/woocommerce/class-pizzaro-products.php';
	require get_template_directory() . '/inc/woocommerce/class-pizzaro-wc-helper.php';
	require get_template_directory() . '/inc/woocommerce/pizzaro-woocommerce-template-hooks.php';
	require get_template_directory() . '/inc/woocommerce/pizzaro-woocommerce-template-functions.php';
	require get_template_directory() . '/inc/woocommerce/integrations.php';
}

if( is_wp_store_locator_activated() ) {
	require get_template_directory() . '/inc/wp-store-locator/class-pizzaro-wpsl.php';
}

/**
 * One Click Demo Import
 */
if ( is_ocdi_activated() ) {
	require get_template_directory() . '/inc/ocdi/hooks.php';
	require get_template_directory() . '/inc/ocdi/functions.php';
}

if ( is_admin() ) {
	require get_template_directory() . '/inc/admin/class-pizzaro-admin.php';
}

/**
 * Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
 * https://github.com/woothemes/theme-customisations
 */

//

//

//Luke adding cart

   function LTLCreation_Core_Add_to_Cart()
{
    global $product;

echo apply_filters( 'woocommerce_loop_add_to_cart_link',
	sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" data-quantity="%s" class="btn btn-theme btn-theme-transparent %s product_type_%s">%s</a>',
		esc_url( $product->add_to_cart_url() ),
		esc_attr( $product->id ),
		esc_attr( $product->get_sku() ),
		esc_attr( isset( $quantity ) ? $quantity : 1 ),
		$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
		esc_attr( $product->product_type ),
		esc_html( $product->add_to_cart_text() )
	),
$product );
}

//Add surchage

   /**
 * Add a 3% surcharge to your cart / checkout
 * change the $percentage to set the surcharge to a value to suit
 * Uses the WooCommerce fees API
 *
 * Add to theme functions.php
 */
add_action( 'woocommerce_cart_calculate_fees','woocommerce_custom_surcharge' );
function woocommerce_custom_surcharge() {
  global $woocommerce;
 
	if ( is_admin() && ! defined( 'DOING_AJAX' ) )
		return;
 
	$percentage = 0.50;
	$surcharge = $percentage;	
	$woocommerce->cart->add_fee( 'Online order fee', $surcharge, true, 'standard' );
 
}


//Luke Adding Code

add_action( 'woocommerce_thankyou', 'ltl_custom_woocommerce_complete_order_sms' );
//Function to run when an order is completed
function ltl_custom_woocommerce_complete_order_sms( $order_id ) {
		global $woocommerce;
        //DEFINE WP OPTIONS NAMES
            
           


	    if ( !$order_id )
	    return;
		$order = new WC_Order( $order_id );

        //getting phone Number//
        $orderInfo = wc_get_order($order_id);
        $order_data = $orderInfo->get_data(); // The Order data
        $order_billing_phone = $order_data['billing']['phone'];


        //var_dump($order);
        //Empty product list
        $product_list = '';
        //Get Order Items
        $order_item = $order->get_items();











        //Get the product name and Quantity for each item ordered
        foreach( $order_item as $product ) {
        	//$meta = WC()->cart->get_item_data( $product );
            //$meta = new WC_Order_Item_Meta( $product );

            //$extra = implode(":", $meta);



            $prodct_name[] = $product['name']."x".$product['qty'];
            
            
        }

        

        //get the order status

        $product_list = implode( ',', $prodct_name );
        $status = $order->get_status();
        $order = $order->get_order_number();


        //$order_billing_phone = $order['billing']['phone'];
        //get order number

        //$order_data = $order->get_data(); // The Order data
        //$order_billing_phone = $order->get_billing_phone();
        //$phoneNumber = $order->billing_phone;

      
$ch = curl_init();

/*Local*/
//curl_setopt($ch, CURLOPT_URL, "http://localhost:7000/order/");
/*Live */
curl_setopt($ch, CURLOPT_URL, "https://getjuicyordersystem.herokuapp.com/neworder/fullorder");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "{
	\n\t\"orderId\": \"$order\"\n}"
	);
curl_setopt($ch, CURLOPT_POST, 1);

$headers = array();
$headers[] = "Content-Type: application/json";
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}

//echo $result;



    }


    //

    /*THIS FUNCTION BELOW CAN UPDATE MINI CART */

   function mode_theme_update_mini_cart() {
  echo wc_get_template( 'cart/mini-cart.php' );
  die();
}
add_filter( 'wp_ajax_nopriv_mode_theme_update_mini_cart', 'mode_theme_update_mini_cart' );
add_filter( 'wp_ajax_mode_theme_update_mini_cart', 'mode_theme_update_mini_cart' );


//Min Order Amount
add_action( 'woocommerce_checkout_process', 'wc_minimum_order_amount' );
// add_action( 'woocommerce_before_cart' , 'wc_minimum_order_amount' );
 
function wc_minimum_order_amount() {
    // Set this variable to specify a minimum order value
    $minimum = 8.00;

    if ( WC()->cart->total < $minimum ) {

        if( is_cart() ) {

            wc_print_notice( 
                sprintf( 'You must have an order with a minimum of %s to place your order, your current order total is %s.' , 
                    wc_price( $minimum ), 
                    wc_price( WC()->cart->total )
                ), 'error' 
            );

        } else {

            wc_add_notice( 
                sprintf( 'You must have an order with a minimum of %s to place your order, your current order total is %s.' , 
                    wc_price( $minimum ), 
                    wc_price( WC()->cart->total )
                ), 'error' 
            );

        }
    }

}



//Adding notice
add_action( 'woocommerce_before_checkout_form', 'skyverge_add_checkout_content', 12 );
function skyverge_add_checkout_content() {
    echo '<div class="gjorderinfo">Note: Pre-order from 4pm, choose a time slot for collection. You will have to enter your address to see if you\'re eligible!</div>';
}

//Adding notice
/*add_action( 'woocommerce_before_checkout_form', 'gj_add_checkout_content', 12 );
function gj_add_checkout_content() {
    echo '<div class="gjorderinfo">You order email will be emailed to you. Please add orders@pricesfishandchips.co.uk to your approved senders list, please check the online system for your order status and when your order is ready for collection</div>';
}*/


//
			
add_filter( 'woocommerce_checkout_fields', 'webendev_woocommerce_checkout_fields' );
/**
 * Change Order Notes Placeholder Text - WooCommerce
 * 
 */
function webendev_woocommerce_checkout_fields( $fields ) {

    $fields['order']['order_comments']['placeholder'] = 'Add Delivery instructions if necessary or salt, vinegar & lemon instructions.';
    return $fields;
}




///////////////Adding shop hours////////////////////
// Trigger Holiday Mode

add_action ('init', 'checkOpen');

function checkOpen(){

    date_default_timezone_set('Europe/London');

    $paymentDate = date('H:i:s');
    $paymentDate=date('H:i:s', strtotime($paymentDate));;
    //echo $paymentDate; // echos today! 
    //$contractDateBegin = date('H:i:s', strtotime("17:15:00"));

    $contractDateBegin = date('H:i:s', strtotime("08:00:00"));

    $contractDateEnd = date('H:i:s', strtotime("20:30:00"));

    $whatDay = date('w');

    /*if ( $whatDay == 1){
        $contractDateEnd = date('H:i:s', strtotime("20:30:00"));
    }

    if ( $whatDay == 4){
        $contractDateEnd = date('H:i:s', strtotime("20:30:00"));
    }
    if ( $whatDay == 5){
        //testing printer
        //$contractDateBegin = date('H:i:s', strtotime("09:00:00"));
        $contractDateEnd = date('H:i:s', strtotime("20:30:00"));
    }
    if ( $whatDay == 6){
        $contractDateEnd = date('H:i:s', strtotime("19:30:00"));
    }*/

    switch ($whatDay) {
        case 0:
        $contractDateEnd = date('H:i:s', strtotime("19:30:00"));
            break;
        case 1:
        $contractDateEnd = date('H:i:s', strtotime("20:30:00"));
            break;
        case 2;
        $contractDateEnd = date('H:i:s', strtotime("20:30:00"));
            break;
        case 3;
        $contractDateEnd = date('H:i:s', strtotime("20:30:00"));
            break;
        case 4;
        $contractDateEnd = date('H:i:s', strtotime("20:30:00"));
            break;
        case 5;
        $contractDateEnd = date('H:i:s', strtotime("20:30:00"));
            break;
        case 6;
        $contractDateEnd = date('H:i:s', strtotime("20:30:00"));
            break;
    }

    if (
        ($paymentDate > $contractDateBegin) && 
        ($paymentDate < $contractDateEnd) 
        //&& ($whatDay > 0 )
        ) {

       

    } else {
         bbloomer_woocommerce_holiday_mode();
    }
}
 


 
 
// Disable Cart, Checkout, Add Cart
 
function bbloomer_woocommerce_holiday_mode() {
 remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
 remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
 remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
 remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
 add_action( 'woocommerce_before_main_content', 'bbloomer_wc_shop_disabled', 9 );
 add_action( 'woocommerce_before_cart', 'bbloomer_wc_shop_disabled', 9 );
 add_action( 'woocommerce_before_checkout_form', 'bbloomer_wc_shop_disabled', 9 );
}
 
 
// Show Holiday Notice
 
function bbloomer_wc_shop_disabled() {
        wc_print_notice( 'Our Online Shop is Closed Currently. We open at 10:15am :)', 'error');
}


//remove//
add_filter( 'wc_add_to_cart_message', 'remove_add_to_cart_message' );

function remove_add_to_cart_message() {
    return;
}


//change create account
function my_text_strings( $translated_text, $text, $domain ) {
    switch ( $translated_text ) {
        case 'Create an account?' :
            $translated_text = __( 'Create an account for quicker checkout in the future', 'woocommerce' );
            break;
    }
    return $translated_text;
}

add_filter( 'gettext', 'my_text_strings', 20, 3 );




/**
 * Change min password strength.
 *
 */
/*function iconic_min_password_strength( $strength ) {
    return 2;
}
 
add_filter( 'woocommerce_min_password_strength', 'iconic_min_password_strength', 10, 1 );*/


function iconic_remove_password_strength() {
   wp_dequeue_script( 'wc-password-strength-meter' );
}
add_action( 'wp_print_scripts', 'iconic_remove_password_strength', 10 );


/*remove images from cart */
// Remove product thumbnail from the cart page
add_filter( 'woocommerce_cart_item_thumbnail', '__return_empty_string' );

/*remove link */
function sv_remove_cart_product_link( $product_link, $cart_item, $cart_item_key ) {
    $product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
    return $product->get_title();
}
add_filter( 'woocommerce_cart_item_name', 'sv_remove_cart_product_link', 10, 3 );


/*adding here*/
/*adding here*/
add_action( 'woocommerce_checkout_update_order_meta', 'saving_checkout_cf_data');
function saving_checkout_cf_data( $order_id ) {

    $recipient_time = $_POST['my_field_name'];
    $chosen_method = $_POST['my_field_name_chosen'];
    if ( ! empty( $recipient_time ) ){
        update_post_meta( $order_id, 'chosen_time', sanitize_text_field( $recipient_time ) );
             /*Remove time */
        if ( $chosen_method === 'Delivery' ){
            global $wpdb;
            $wpdb->query($wpdb->prepare ("DELETE FROM wp_p784c5zva0_deliverytimes WHERE deliveryoption = %s", $recipient_time ));
            error_log( 'deleted');
        } else {
            global $wpdb;
            $wpdb->query($wpdb->prepare ("DELETE FROM wp_p784c5zva0_deliverytimes WHERE collectionoption = %s", $recipient_time ));
        }
    }

   
        
}

/**
 * Process the checkout
 */
add_action('woocommerce_checkout_process', 'my_custom_checkout_field_process');

function my_custom_checkout_field_process() {
    // Check if set, if its not set add an error.
    if ( ! $_POST['my_field_name'] )
        wc_add_notice( __( 'Please choose a delivery or collection time' ), 'error' );
}

/**
 * Display field value on the order edit page
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );

function my_custom_checkout_field_display_admin_order_meta($order){
    echo '<p><strong>'.__('Time chosen').':</strong> ' . get_post_meta( $order->id, 'chosen_time', true ) . '</p>';
}




/*adding new*/

add_action( 'woocommerce_after_checkout_billing_form', 'display_extra_fields_after_billing_address' , 10, 1 );

function display_extra_fields_after_billing_address () 
    {
    //_e( "Choose Delivery Option: ", "add_extra_fields");
    ?>
    <h2 class="steptwo">Step 2 - Choose a Delivery option</h2>
    <br>
    <button type="button" id="paiddeliverygj" class="delivbutton btn btn-primary paiddelivery">£2.00 Delivery</button>
    <button type="button" class="delivbutton gjcollection btn btn-primary">Collection</button>
    <button type="button" id="freedelivery" class="delivbutton btn btn-primary">Free Delivery</button>
    <br />

    <style>
        .timeselectdelivery{
            display: none
        }
        .timeselectcollection{
            display: none;
        }
        .delivbutton{
            width: 30%;
            float: left;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .gjhidden{
            /*display:none;*/
        }
        .gjhidden input{
            background-color: grey;
            color: #ffffff;
        }
        .timeselectcollection h2{
            font-size: 12px;
        }
        .timeselectdelivery h2{
            font-size: 12px
        }
        #my_custom_checkout_field h2{
            font-size: 12px;
        }
        #gjfinaltime, #gjfinalmethod{
            background-color: #143b7c;
            width: 50%;
        }
        #gjfinaltime:focus, #gjfinalmethod:focus{
            color: #ffffff;
        }
        #gjfinaltime_field label{
            font-style: italic;
        }
        #gjfinalmethod_field label{
            font-style: italic;
        }
        .steptwo{
            font-size: 14px;
            text-decoration: underline;
        }
        .pizzaro-order-steps{
            display: none;
        }
        #gjfinalmethod_field span.optional,  #gjfinaltime_field span.optional{
            display: none;
        }

    </style>

    
    <div class="timeselectcollection">
        <h6 class="steptwo">Step 3 - Choose a time</h3>
        <h2>Collection chosen - Select a time below</h2>
            <select name="add_delivery_date" class="custom-select timeselectcollection">
                <option selected>Choose a Collection time</option>
                 <!--only show if greater than 5:15pm -->
                <?php 
                $paymentDate = date('H:i:s');
                $paymentDate=date('H:i:s', strtotime($paymentDate));
                $contractDateEnd = date('H:i:s', strtotime("17:15:00"));
                if ($paymentDate >= $contractDateEnd ){
                    ?>
                     <option value="ASAP">ASAP</option>
               <? }
                ?>

                    <?php 
                    global $wpdb;
                    $myrows = $wpdb->get_results( "SELECT DATE_FORMAT(collectionoption,'%H:%i')
                    From wp_p784c5zva0_deliverytimes
                    Where collectionoption >= NOW() + INTERVAL 30 MINUTE"); 
                    //90 for summer time
    
                    foreach($myrows as $row => $innerArray){
       
                    foreach($innerArray as $innerRow['timeoption'] => $value){ ?>
                    <option value="<?php echo $value; ?>"><?php echo $value; ?></option>
         
                 <?php }
                    }
    ?>
               

    </select>
    </div>

    <div class="timeselectdelivery">
    <h6 class="steptwo">Step 3 - Choose a time</h3>
    <h2>Delivery chosen - Select a time below</h2>
    <select name="add_delivery_date" class="custom-select ">
        
    <option selected>Choose a Delivery time</option>
    <!--if after 5:15pm needed -->
      <?php 
                $paymentDate = date('H:i:s');
                $paymentDate=date('H:i:s', strtotime($paymentDate));
                $contractDateEnd = date('H:i:s', strtotime("17:15:00"));
               /* if ($paymentDate >= $contractDateEnd ){
                    ?>
                     <option value="ASAP">ASAP</option>
               <? }*/
                ?>
    <?php 
    global $wpdb;
    $myrows = $wpdb->get_results( "SELECT DATE_FORMAT(deliveryoption,'%H:%i')
    From wp_p784c5zva0_deliverytimes
    Where deliveryoption >= NOW() + INTERVAL 20 MINUTE"); 
    
    foreach($myrows as $row => $innerArray){
       
        foreach($innerArray as $innerRow['timeoption'] => $value){ ?>
            <option value="<?php echo $value; ?>"><?php echo $value; ?></option>
         
       <?php 
       }
      } ?>
      
    ?>
    </select>
    </div>
    <br />
    <div class="gjwarningcollection">
        <p><i>Meal completion time will vary depending on busy periods in store. By selecting the "ASAP" option, you confirm that you understand we will put your order straight into a chronological queue alongside in store orders. We operate on a first come first serve basis so there will be a wait during peak times. You will receive a text when your order is ready.</i></p>
    </div>

    

    <script>
        jQuery(document).ready(function( $ ) {
           /* $( ".add_delivery_date").datepicker( {
                minDate: 0, 
            } );*/
            $(".custom-select").change(function(){
                //alert(this.value);
                $('#gjfinaltime').val(this.value);
                //only show asap if chosen asap?
            });

            

            $( "#freedelivery" ).click(function() {
               console.log("clicked");
               $('#gjfinaltime').val('');
               $('#gjfinalmethod').val('Free Delivery');
               $(".timeselectcollection").css("display", "none");
               $(".timeselectdelivery").css("display", "block");
               $("#shipping_method_0_free_shipping5").prop("checked", true)
               jQuery(document.body).trigger("update_checkout");

                });
            


            $( ".paiddelivery" ).click(function() {
               console.log("clicked");
               $('#gjfinaltime').val('');
               $('#gjfinalmethod').val('Delivery');
               $(".timeselectcollection").css("display", "none");
               $(".timeselectdelivery").css("display", "block");
               $("#shipping_method_0_flat_rate1").prop("checked", true)
               jQuery(document.body).trigger("update_checkout");

                });
            $( ".gjcollection" ).click(function() {
               console.log("clicked");
               $('#gjfinaltime').val('');
               $('#gjfinalmethod').val('Collection');
               $(".timeselectcollection").css("display", "block");
               $(".timeselectdelivery").css("display", "none");
               $("#shipping_method_0_local_pickup2").prop("checked", true)
               jQuery(document.body).trigger("update_checkout");
             
                });

            //$(".timeselect")
         } );


    jQuery( document ).ajaxComplete(function() {

        if (document.getElementById('shipping_method_0_flat_rate1')){
            console.log('Disable pick up')
            document.getElementById("shipping_method_0_local_pickup2").disabled = true;
        }
        if ( document.getElementById('shipping_method_0_flat_rate1')){
            document.getElementById('shipping_method_0_flat_rate1').disabled = true;
        }
        if ( document.getElementById('shipping_method_0_free_shipping5')){
            document.getElementById('shipping_method_0_free_shipping5').disabled = true;
        }

      
       
        document.getElementById('gjfinaltime').readOnly = true;
        document.getElementById('gjfinalmethod').readOnly = true;
        
        

            if( jQuery('body.woocommerce-checkout').length){

                if (document.getElementById('shipping_method_0_flat_rate1')){
                        console.log('Paid delivery available')
                        document.getElementById("paiddeliverygj").style.display = "block"
                    }
                 else {
                    console.log('Paid delivery NOT available')
                    document.getElementById("paiddeliverygj").style.display = "none"
                 }   
                if (document.getElementById('shipping_method_0_local_pickup2')){
                        console.log('Paid collection available')
                    }
                if (document.getElementById('shipping_method_0_free_shipping5')){
                        console.log('Free delivery available')
                        document.getElementById("paiddeliverygj").style.display = "none"
                        document.getElementById("freedelivery").style.display = "block"
                } else {
                    document.getElementById("freedelivery").style.display = "none"
                }       

                                


            }
            });
    </script>
    <?php 
}

/**
 * Add the field to the checkout
 */
add_action( 'woocommerce_after_checkout_billing_form', 'my_custom_checkout_field' );

function my_custom_checkout_field( $checkout ) {

    echo '<div id="my_custom_checkout_field"><h2>' . __('Your Chosen Time') . '</h2>';

    woocommerce_form_field( 'my_field_name', array(
        'type'          => 'text',
        'readonly'      => 'readonly',
        'class'         => array('my-field-class form-row-wide gjhidden'),
        'id'            => 'gjfinaltime',
        'label'         => __('Cannot edit this field, choose times above'),
        'placeholder'   => __(''),
        ), $checkout->get_value( 'my_field_name' ));

    echo '</div>';

}

/**
 * Add the field to the checkout
 */
add_action( 'woocommerce_after_checkout_billing_form', 'my_custom_checkout_field_chosen' );

function my_custom_checkout_field_chosen( $checkout ) {

    echo '<div id="my_custom_checkout_field"><h2>' . __('Your Chosen Method') . '</h2>';

    woocommerce_form_field( 'my_field_name_chosen', array(
        'type'          => 'text',
        'readonly'      => 'readonly',
        'class'         => array('my-field-class form-row-wide gjhidden'),
        'id'            => 'gjfinalmethod',
        'label'         => __('Cannot edit this field, choose times method above'),
        'placeholder'   => __(''),
        ), $checkout->get_value( 'my_field_name_chosen' ));

    echo '</div>';

}




















