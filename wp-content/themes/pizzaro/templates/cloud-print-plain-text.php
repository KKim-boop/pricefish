<head>

<!--

This template implements a basic, all-purpose plain-text print-out of the customer's order and delivery details. It is used if the 'plain text' option is selected in the plugin (available since version 2.3.0). You may wish to customise it to suit your needs.

Note that there is a tiny amount of pseudo-markup in this file; only the body section is used for the output. So, you can put anything in this head section (e.g. comments, like these).

IMPORTANT: If editing this file, then do not edit it directly within the plugin - if you do, then you will lose all of your changes when you update the plugin. Instead, copy it, and place it as templates/cloud-print.php within the folder of either your child theme or your theme. Alternatively, use the filter woocommerce_printorders_printtemplate to indicate a different location.

Available variables: $order (WooCommerce order object) and (redundant / for convenience - can be gained from $order), $order_id and $order_items ( = $order->get_items()).

You can use a third parameter to get_detail() of 'billing_' to get billing addresses instead of shipping, if preferred.

Master template last edited: 7th March 2017

-->

</head>
<body><?php

do_action('woocommerce_cloudprint_internaloutput_header', $order, 'text/plain'); ?>

<?php //order time //>

/*$paid_date = $this->get_date_paid($order)

if ($paid_date) {
	echo $paid_date
}
 ?>*/ ?>

 <?php 

$order_data = $order->get_data();
$order_billing_first_name = $order_data['billing']['first_name'];
$order_billing_last_name = $order_data['billing']['last_name'];
$order_date_created = $order_data['date_created']->date('Y-m-d H:i:s'); 
//$completed = $order->get_date_completed(); 
$order_time_chosen = get_post_meta( $order_id, 'chosen_time');
?>



<?php echo "Name: ".$order_billing_first_name.' '.$order_billing_last_name; 
echo "\n" ?>


<? $shipping_method_title = $order->get_shipping_method();
if (!empty($shipping_method_title)) echo 'Method: '.strip_tags($shipping_method_title); ?>

<?php echo 'Time: '.$order_time_chosen[0] ?>




<?php
$phone = $this->get_detail($order, 'phone', 'billing_');
if (!empty($phone)) echo "\n".__('Phone', 'woocommerce').": ".$phone;
?>


<?php printf(__( 'Order number: %s', 'woocommerce'), $order->get_order_number());?>

<?php

$completed_date = $this->get_order_date($order);

echo sprintf(__('Order date: %s', 'woocommerce'), strip_tags(date_i18n(wc_date_format(), $completed_date)))."\n";

$customer_note = is_callable(array($order, 'get_customer_note')) ? $order->get_customer_note() : $order->customer_note;

if ($customer_note) {
	echo __('Customer Note:', 'woocommerce').' '.$customer_note."\n";
}

if (!is_array($order_items)) return;

$total_without_tax = 0;
$total_tax = 0;

$line_items = '';
global $woocommerce;

foreach ($order_items as $itemkey => $item) {

	// Interesting keys: name, type ( = 'line_item' for both single + variable products in our tests), qty, product_id, variation_id (present but empty for simple items), line_subtotal, line_total, line_tax, line_subtotal_tax
	// Then there are keys for the variations, e.g. Meat => Lamb
	// $customer_info .= '<pre>'.print_r($item, true).'</pre>';

	if ($item['type'] != 'line_item') {
		$line_items .= "Error: Item was not a line item: ".$itemkey."\n";
		continue;
	}

	$qty = $item['qty'];

	$product = $order->get_product_from_item($item);

	# Interesting keys: price, post->post_title, product_type=simple|variation, (if variation) variation_data => array('attribute_meta' => 'prawn')
	# Could be: WC_Product_Simple, WC_Product_Variation, etc.

	if (!$product->exists()) continue;

	$item_name = apply_filters('woocommerce_print_orders_item_name', $product->get_title(), $product, 'text/plain');

	

	//$total_tax += $item['line_tax'];
	//$total_without_tax += $item['line_total'];
	//$cost = $item['line_total']+$item['line_tax'];
	//$price = $cost/(max($qty, 1));

	$sku = $product->get_sku();
	if ($sku) $item_name .= " ($sku)";

	$line_items .= "<strong>".$qty.'x'.$item_name."</strong>"."\n";



	
	if (version_compare($woocommerce->version, '2.4', 'ge')) {
		$item_meta = new WC_Order_Item_Meta( $item );
	} else {
		$item_meta = new WC_Order_Item_Meta( $item['item_meta'] );
	}
	/*meta changes*/



	$imeta_output = $item_meta->display(true, true);
	//echo $imeta_output;

	if ($imeta_output) {
		$line_items .= apply_filters('woocommerce_print_orders_item_meta', nl2br($imeta_output), "\n".$item, 'text/plain')."\n";


			/*foreach ( $item[ 'item_meta' ] as $key => $value ) {
											if( !( 0 === strpos( $key, '_' ) ) ) {
												$line_items .= "\n"; . $key . ':' . $value;
											}
										}*/
	}

	$line_items .= "\n";
}




echo "\n";


if ($order_shipping_tax) $line_items .= "\n".sprintf(apply_filters('woocommerce_print_orders_text_shipping', __('Shipping', 'woocommerce'), 'text/plain').' Tax: %2.2f', $order_shipping_tax);

echo $line_items;

?>
<?php 

	$order_total = is_callable(array($order, 'get_total')) ? $order->get_total() : $order->order_total;

	_e('Total:', 'woocommerce');?> <?php echo $order_total;?>, <?php
	
	$payment_method_title = is_callable(array($order, 'get_payment_method_title')) ? $order->get_payment_method_title() : $order->payment_method_title;
	
	echo strip_tags($payment_method_title);

	
?>

<?php /*Shipping or Delivery */  

echo "\n";


$shipping_method_title = $order->get_shipping_method();
if (!empty($shipping_method_title)) echo 'Method: '.strip_tags($shipping_method_title);

echo "\n";
echo "Address:";
echo "\n";
//$shipping_address = $order->get_address();
//echo $shipping_address;
$order_billing_address_1 = $order_data['billing']['address_1'];
$order_billing_address_2 = $order_data['billing']['address_2'];
$order_billing_city = $order_data['billing']['city'];
$order_billing_state = $order_data['billing']['state'];
$order_billing_postcode = $order_data['billing']['postcode'];
$order_billing_country = $order_data['billing']['country'];

echo $order_billing_address_1 . "\n";
echo $order_billing_address_2 . "\n";	
echo $order_billing_city . "\n";
echo $order_billing_state . "\n";
echo $order_billing_postcode;
 		

?>


<?php do_action('woocommerce_cloudprint_internaloutput_footer', $order, 'text/plain'); ?>
</body>
