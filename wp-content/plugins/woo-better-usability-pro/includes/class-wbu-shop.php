<?php
if ( !class_exists('WBU_Shop')) {
class WBU_Shop {
    function init_hooks() {
        add_filter('woocommerce_product_add_to_cart_text', array($this, 'add_to_cart_text') );
        add_filter('woocommerce_add_to_cart_redirect', array($this, 'add_to_cart_redirect') );
        add_filter('woocommerce_loop_add_to_cart_link', array($this, 'shop_quantity_input'), 10, 2 );
        add_filter('woocommerce_blocks_product_grid_item_html', array($this, 'shop_gutenberg_quantity_input'), 10, 3 );
        add_filter('loop_shop_columns', array($this, 'product_columns'), 10 );
        add_filter('woocommerce_get_script_data', array($this, 'cart_fragment_params') );
        add_filter('wc_get_template', array($this, 'wc_get_template'), 10, 2 );

        // try to identify if the current theme is Divi theme, so make compatibility for the "Allow change quantity on Shop" feature
        // dont know if this is the best way to check this, so please open new support if you have anything better
        if ( substr_count(strtolower(get_stylesheet()), 'divi') > 0 ) {
            add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 20 );
        }
    }
    
    function add_to_cart_text($text) {
        global $product;

        if ( wbu()->option('enable_direct_checkout') == 'yes' ) {
            if ( $product instanceof WC_Product_Simple ) {
                return wbu()->option('direct_checkout_add_cart_text');
            }
        }

        return $text;
    }
    
    function add_to_cart_redirect($url) {
        // wbu()->is_shop_loop_new() && 
        
        if ( wbu()->option('enable_direct_checkout') == 'yes' ) {
            return wc_get_checkout_url();
        }

        return $url;
    }
    
    function cart_fragment_params($params) {

        if ( wbu()->is_shop_loop() && wbu()->option('enable_direct_checkout') == 'yes' ) {
            $params['i18n_view_cart'] = wbu()->option('replace_view_cart_text');
        }

        return $params;
    }
    
    function product_columns($columns) {
        if ( wbu()->option('shop_change_products_per_show') == 'yes' ) {
            return wbu()->option('shop_products_per_row');
        }

        return $columns;
    }

    function product_allowed_change_qty($product) {
        // check the front page ignore config
        if ( !apply_filters('wbu_enable_quantity_input', true) ) {
            return false;
        }

        $allowChangeQty = ( wbu()->option('enable_quantity_on_shop') == 'yes' ) &&
                            $product &&
                            $product->is_type('simple') &&
                            $product->is_in_stock() &&
                            $product->is_purchasable() &&
                            !$product->is_sold_individually();

        return $allowChangeQty;
    }

    function shop_build_quantity_area_html($product) {
        $useSelectField = ( wbu()->option('qty_as_select_shop') == 'yes' );
            
        $quantityField = wbu()->get_template('shop-qty-field.php', array(
            'product' => $product,
            'useSelectField' => $useSelectField,
            'inputValue' => apply_filters('wbu_shop_quantity_value', 1, $product),
        ));
        
        if ( wbu()->option('show_show_quantity_buttons') == 'yes' && $product->is_type('simple') && !class_exists('Avada') ) {
            $qtyWithoutDiv = str_replace(array('<div class="quantity">', '</div>'), array('', ''), $quantityField);
            $quantityField = wbu()->get_template('qty-buttons-shop.php', array('input' => $qtyWithoutDiv));
        }

        // make compatible with 3rd plugins like WooCommerce Min/Max Quantities
        $initialQuantity = 1;
        $qtyInputArgs = apply_filters( 'woocommerce_quantity_input_args', array(), $product );
        
        if ( !empty($qtyInputArgs['input_value']) ) {
            $initialQuantity = $qtyInputArgs['input_value'];
        }
        
        $qtyAreaArgs = apply_filters('wbu_shop_quantity_area_args', array(
            'product' => $product,
            'allowChangeQty' => true,
            'quantityField' => $quantityField,
            'class' => 'button alt ajax_add_to_cart add_to_cart_button product_type_simple',
            'initialQuantity' => $initialQuantity,
        ));
        
        $html = wbu()->get_template('shop-qty-area.php', $qtyAreaArgs);

        return $html;
    }
    
    function shop_quantity_input($html, $product) {
        if ( $this->product_allowed_change_qty($product) ) {
            return $this->shop_build_quantity_area_html($product);
        }

        return $html;
    }

    // workaround to support woo-gutenberg-products-block plugin
    function shop_gutenberg_quantity_input($html, $data, $product) {
        if ( $this->product_allowed_change_qty($product) ) {
            $divStart = '<div class="wp-block-button wc-block-grid__product-add-to-cart">';
            $newHtml = $this->shop_build_quantity_area_html($product);

            return preg_replace('/'.$divStart.'(.*)<\/div>/', $divStart.$newHtml.'</div>', $html);
        }

        return $html;
    }

    function wc_get_template( $located, $template_name ) {    

        if ( $template_name == 'loop/result-count.php' &&  wbu()->option('hide_shop_paginator') == 'yes' ) {
            $located = wbu()->template_path('empty.php');
        }
        else if ( $template_name == 'loop/orderby.php' && wbu()->option('hide_shop_sorting') == 'yes' ) {
            $located = wbu()->template_path('empty.php');
        }

        return $located;
    }
}
}
