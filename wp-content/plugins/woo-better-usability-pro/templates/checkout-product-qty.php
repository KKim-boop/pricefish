<?php
if ( ! defined( 'ABSPATH' ) ) exit;

echo apply_filters('wbu_checkout_quantity_input', woocommerce_quantity_input(array(
    'input_name'  => "cart[{$cart_item_key}][qty]",
    'input_value' => (int) $cart_item['quantity'],
    'max_value'   => ( $product->backorders_allowed() ? '' : $product->get_stock_quantity() ),
    'min_value'   => '1'
), $product, false ));

