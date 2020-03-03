<?php
if ( !class_exists('WBU_Checkout')) {
class WBU_Checkout {
    function init_hooks() {
        add_action('wp_ajax_nopriv_wbu_update_checkout', array($this, 'update_checkout') );
        add_action('wp_ajax_wbu_update_checkout', array($this, 'update_checkout') );
        add_filter('woocommerce_checkout_cart_item_quantity', array($this, 'checkout_item_qty'), 10, 2 );
        add_filter('woocommerce_cart_item_name', array($this, 'cart_item_name'), 11, 3 );
    }
    
    function update_checkout() {
        $values = array();
        parse_str($_POST['post_data'], $values);

        $cart = (array) $values['cart'];

        foreach ( $cart as $cart_key => $cart_value ){
            if ( empty($cart_value['qty']) || !is_numeric($cart_value['qty']) ) {
                continue;
            }

            WC()->cart->set_quantity( $cart_key, $cart_value['qty'], false );
            WC()->cart->calculate_totals();
            woocommerce_cart_totals();
        }

        exit;
    }
    
    function checkout_item_qty($cart_item, $cart_item_key) {
        if ( !is_checkout() || ( wbu()->option('checkout_allow_change_qty') != 'yes' ) ) {
            return $cart_item;
        }

        return '';
    }
    
    function cart_item_name($product_title, $cart_item, $cart_item_key) {
        $allowChangeQty = wbu()->option('checkout_allow_change_qty');
        $allowDelete = wbu()->option('checkout_allow_delete');

        if ( !is_checkout() || ( !in_array('yes', array($allowChangeQty, $allowDelete))) ) {
            return $product_title;
        }

        return wbu()->get_template('checkout-product.php', array(
            'product_id' => $cart_item['product_id'],
            'product' => $cart_item['data'],
            'displayUnitPrice' => wbu()->option('checkout_display_unit_price'),
            'allowChangeQty' => $allowChangeQty,
            'allowDelete' => $allowDelete,
            'product_title' => $product_title,
            'cart_item' => $cart_item,
            'cart_item_key' => $cart_item_key,
        ));
    }
    
    function form_settings() {
        ?>
        <?php wbu()->settings->form_header() ?>
        <?php wbu()->settings->form_footer() ?>
        <?php
    }
}
}
