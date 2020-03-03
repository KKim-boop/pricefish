<html>
<head>
<?php

/*

This template implements a basic, all-purpose print-out of the customer's order and delivery details. You may wish to customise it to suit your needs. If you only wish to make minor ammendments to this basic template, then you can also use WordPress filters. For basic formatting changes, you may only need to take note of the CSS classes and IDs used below and add a style-sheet.

IMPORTANT: If editing this file, then do not edit it directly within the plugin - if you do, then you will lose all of your changes when you update the plugin. Instead, copy it, and place it as templates/cloud-print.php within the folder of either your child theme or your theme. Alternatively, use the filter woocommerce_printorders_printtemplate to indicate a different location.

Available variables: $order (WooCommerce order object) and (redundant / for convenience - can be gained from $order), $order_id and $order_items ( = $order->get_items()).

You can use a third parameter to get_detail() of 'billing_' to get billing addresses instead of shipping, if preferred.

Master template last edited: 6th June 2017

*/

if (!defined('ABSPATH')) die('No direct access allowed');
?>
<style type="text/css">
	/* "When rendering with the core fonts dompdf only supports characters that are covered by the Windows ANSI encoding" - https://github.com/dompdf/dompdf/issues/626.
	So, if printing other characters, you should specify another font - as we do here (specifying DejaVu Serif)
	*/
	html, body { font-family: DejaVu Serif, sans-serif; } 
	p.itemmeta { font-size: 13px; padding: 0 0 0 20px; margin: 1px; }
	html, body, p, h1 {font-size: 13px;}
	.line-item-firstline {font-size: 25px; font-weight: 900;}

	body {margin-bottom: 10px; margin-left: -15px; text-align: left; width: 300px}

	
	
</style>
</head>
<body>

	<?php

if (!is_array($order_items)) return;

$total_without_tax = 0;
$total_tax = 0;

$line_items = '';
global $woocommerce;

foreach ($order_items as $itemkey => $item) {

	// Interesting keys: name, type ( = 'line_item' for both single + variable products in our tests), qty, product_id, variation_id (present but empty for simple items), line_subtotal, line_total, line_tax, line_subtotal_tax
	// Then there are keys for the variations, e.g. Meat => Lamb
	// $customer_info .= '<pre>'.print_r($item, true).'</pre>';

	$line_items .= '<p class="line-item">';

	if ($item['type'] != 'line_item') {
		$line_items .= "Error: Item was not a line item: ".htmlspecialchars($itemkey)."</p>";
		continue;
	}

	$qty = $item['qty'];

	$product = $order->get_product_from_item($item);

	# Interesting keys: price, post->post_title, product_type=simple|variation, (if variation) variation_data => array('attribute_meta' => 'prawn')
	# Could be: WC_Product_Simple, WC_Product_Variation, etc.

	if (!$product->exists()) continue;

	$item_name = apply_filters('woocommerce_print_orders_item_name', $product->get_title(), $product, 'text/html');

	

	
	//$total_tax += $item['line_tax'];
	//$total_without_tax += $item['line_total'];
	//$cost = $item['line_total']+$item['line_tax'];
	//$price = $cost/(max($qty, 1));

	$sku = $product->get_sku();
	if ($sku) $item_name .= " ($sku)";

	$line_items .= '<span class="line-item-firstline">'.$qty.'x'.$item_name."<br></span>\n";
	
	$line_items .= '<p class="itemmeta">';
	$imeta_output = false;
	
	// WC 3.0+ - this isn't suitable; see: https://github.com/woocommerce/woocommerce/issues/14623
	if (0 && function_exists('wc_display_item_meta')) {
	
		$imeta_output = wc_display_item_meta($item, array('echo' => false));
		
	} else {
	
		if (version_compare($woocommerce->version, '2.4', 'ge')) {
			$item_meta = new WC_Order_Item_Meta( $item );
		} else {
			$item_meta = new WC_Order_Item_Meta( $item['item_meta'] );
		}
		
		$imeta_output = $item_meta->display(true, true);
	
	}
	
	if ($imeta_output) {
		$line_items .= apply_filters('woocommerce_print_orders_item_meta', nl2br($imeta_output), $item, 'text/html');
	}
	
	$line_items .= "</p>\n";
}

$line_items .= '<p id="charges">';

$order_tax = is_callable(array($order, 'get_cart_tax')) ? $order->get_cart_tax() : $order->order_tax;

if ($order_tax) {
	$tax_line = sprintf('Tax: %2.2f', $order_tax)."<br>\n";
	$line_items .= apply_filters('woocommerce_print_orders_tax_line', $tax_line, $order_tax, 'text/html');
}

$order_shipping = is_callable(array($order, 'get_shipping_total')) ? $order->get_shipping_total() : $order->order_shipping;
$order_shipping_tax = is_callable(array($order, 'get_shipping_tax')) ? $order->get_shipping_tax() : $order->order_shipping_tax;

if ($order_shipping) $line_items .= sprintf(apply_filters('woocommerce_print_orders_text_shipping', __('Shipping', 'woocommerce'), 'text/html').': %2.2f', $order_shipping)."<br>\n";
if ($order_shipping_tax) $line_items .= sprintf(apply_filters('woocommerce_print_orders_text_shipping', __('Shipping', 'woocommerce'), 'text/html').' Tax: %2.2f', $order_shipping_tax)."\n";

$line_items .= '</p>';


$order_total = is_callable(array($order, 'get_total')) ? $order->get_total() : $order->order_total;

?>

<?php 

	//paid date//
$paid_date = $order->get_date_paid()
//
	$order_data = $order->get_data();
$order_billing_first_name = $order_data['billing']['first_name'];
$order_billing_last_name = $order_data['billing']['last_name'];
$order_date_created = $order_data['date_created']->date('Y-m-d H:i:s');
//$message = $order->customer_message
 ?>




	<!-- Adding table-->
	<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="">
		<tr>
        <td align="" valign="top">
            <table border="0" cellpadding="5" cellspacing="0" width="100%" id="">
                <tr>
                    <td align="" valign="">
                        Name: <?php echo $order_billing_first_name.' '.$order_billing_last_name; ?>
                    </td>
                </tr>
                <tr>    
                     <td align="" valign="">
                        Order Time: <?php echo  $order_date_created ?>
                    </td>
                 </tr>
                 <tr>
                    <td>
					                    	<?php
						$phone = $this->get_detail($order, 'phone', 'billing_');
						if (!empty($phone)) echo "<br>\n".__('Phone', 'woocommerce').": ".htmlspecialchars($phone);
						?>
					</td>
				</tr>

                
            </table>
            <table width="">
            	<tr>

            		<td>
            			Order Notes:  <?php echo $order->customer_note  ?>
            		</td>
            		
            	</tr>
            	<tr>
            		<td>
<p class="line-item-firstline"><?php printf(__( 'Order no: %s', 'woocommerce'), '</b>'.htmlspecialchars($order->get_order_number()));?></p>
            		</td>
            		
            	</tr>

            </table>
            <table border="0" width="">
            	<tr>
            		<td class="orderitems">

            			<b class="line-item-firstline">Items:</b> <br />
            			<b><?php echo $line_items; ?></b>
            		</td>
            	</tr>
            </table>
             <table width="">
            	<tr>
            		<td>
            			<p id="order-total">
	<b><?php _e('Total:', 'woocommerce');?></b>
	<span id="order-total-price"><?php echo $order_total;?></span>, 
	<?php
	
		$payment_method_title = is_callable(array($order, 'get_payment_method_title')) ? $order->get_payment_method_title() : $order->payment_method_title;
	
		echo strip_tags($payment_method_title);

		$shipping_method_title = $order->get_shipping_method();
		if (!empty($shipping_method_title)) echo ', '.strip_tags($shipping_method_title);
	?>
</p>
            		</td>
            	</tr>
            </table>

        </td>
    </tr>
	</table>




</body>
</html>
