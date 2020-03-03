<?php
if ( !class_exists('WBU_Cart')) {
class WBU_Cart {
    function init_hooks() {
        add_action('woocommerce_cart_updated', array($this, 'update_cart_prices') );
        add_filter('woocommerce_cart_item_quantity', array($this, 'quantity_buttons'), 10, 3 );
        add_filter('woocommerce_add_to_cart_validation', array($this, 'add_to_cart_validation'), 1, 3);
        add_filter('wc_get_template', array($this, 'get_template_quantity_input'), 10, 2 );            
    }
    
    function get_template_quantity_input( $located, $template_name ) {  
        
        if ( ( $template_name == 'global/quantity-input.php' ) && ( wbu()->option('qty_as_select_cart') == 'yes' ) && is_cart() ) {
            $located = wbu()->template_path('qty-select-cart.php');
        }
        
        return $located;
    }
    
    function update_cart_prices() {

        if ( empty($_POST['is_wbu_ajax']) || (wbu()->option('cart_ajax_method') != 'make_specific_ajax') ) {
            return;
        }

        // avoid conflict with PayPal for WooCommerce plugin
        static $entered = false;

        if ( $entered ) {
            return;
        }
        else {
            $entered = true;
        }
        
        // force to define is in cart page to show shipping calculator and others
        if ( !defined('WOOCOMMERCE_CART') ) {
            define('WOOCOMMERCE_CART', true);
        }

        $resp = array();
        $resp['update_label'] = __( 'Update cart', 'woocommerce' );
        $resp['checkout_label'] = __( 'Proceed to checkout', 'woocommerce' );
        $resp['price'] = 0;

        // calculate the item price
        if ( !empty($_POST['cart_item_key']) ) {
            $cart = WC()->cart;
            $items = $cart->get_cart();
            $cart_item_key = sanitize_text_field($_POST['cart_item_key']);

            if ( array_key_exists($cart_item_key, $items)) {
                $qty = (int) $_POST['cart'][$cart_item_key]['qty'];
                $cart->set_quantity($cart_item_key, $qty);

                $cart_item = $items[$cart_item_key];
                $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

                $price = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $qty), $cart_item, $cart_item_key );

                $resp['price'] = $price;
            }
        }

        // render the cart totals (cart-totals.php)
        ob_start();
        do_action( 'woocommerce_after_cart_table' );
        do_action( 'woocommerce_cart_collaterals' );
        do_action( 'woocommerce_after_cart' );

        $resp['html'] = ob_get_clean();

        echo json_encode($resp);
        exit;
    }
    
    // add the + and - buttons
    function quantity_buttons( $inputDiv, $cart_item_key, $cart_item = null ) { 
        // if 1: check config
        // if 2: some users related duplication problem, so it avoid this
        if ( ( wbu()->option('show_qty_buttons') === 'no' ) || preg_match('/(wbu-qty-button|type=\"hidden\")/', $inputDiv) ) {
            return $inputDiv;
        }

        // pass this vars to template
        $input = str_replace(array('<div class="quantity">', '</div>'), array('', ''), $inputDiv);

        return wbu()->get_template('qty-buttons-cart.php', array(
            'inputDiv' => $inputDiv,
            'cart_item_key' => $cart_item_key,
            'cart_item' => $cart_item,
            'input' => $input,
        ));
    }
    
    function add_to_cart_validation( $passed, $product_id, $quantity ) {
        $productMaxQty = wbu()->option('product_max_qty');
        
        if ( $productMaxQty > 0 ) {
            $newQuantity = ( $quantity + $this->get_cart_quantity($product_id) );
            
            if ( $newQuantity > $productMaxQty ) {
                $passed = false;
                wc_add_notice( __('The maximum quantity that can be added for this product is:', 'woo-better-usability') . ' ' . $productMaxQty, 'error' );
            }
        }
        
        return $passed;
    }
    
    function get_cart_quantity($product_id) {
        $count = 0;
        
        foreach( WC()->cart->get_cart() as $item ){
            if ( $item['product_id'] == $product_id ){
                $count += (int) $item['quantity'];
            }
        }
        
        return $count;
    }
}
}
