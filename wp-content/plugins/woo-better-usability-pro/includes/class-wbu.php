<?php
if ( !class_exists('WBU')) {
class WBU {
    /**
     * @var WBU_Cart
     */
    public $cart;

    /**
     * @var WBU_Checkout
     */
    public $checkout;

    /**
     * @var WBU_Shop
     */
    public $shop;
    
    /**
     * @var WBU_Product
     */
    public $product;
    
    function init_classes() {
        $this->cart = new WBU_Cart();
        $this->checkout = new WBU_Checkout();
        $this->shop = new WBU_Shop();
        $this->product = new WBU_Product();
    }
    
    function init_hooks() {
        // general hooks
        add_action('init', array($this, 'plugin_init'));
        add_action('wp_ajax_wbu_dismissed_notice_handler', array($this, 'ajax_notice_handler') );
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts') );
        add_filter('wbu_shop_quantity_area_args', array($this, 'shop_quantity_area_args'));
        add_filter('wc_add_to_cart_message_html', array($this, 'filter_add_cart_message'));

        add_filter('mh_wbu_settings', array($this, 'wbu_settings'));
        add_filter('mh_wbu_premium_url', array($this, 'wbu_premium_url'));
        
        // initialize subclasses hooks
        $this->shop->init_hooks();
        $this->cart->init_hooks();
        $this->checkout->init_hooks();
        $this->product->init_hooks();
    }

    function wbu_premium_url() {
        return 'https://gumroad.com/l/rLol';
    }

    function wbu_settings($arr) {

        // General tab
        $arr['hide_addedtocart_msg'] = array(
            'label' => __('Hide `Added to cart` message after add product', 'woo-better-usability'),
            'tab' => __('General', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'no',
        );
        $arr['hide_viewcart_link'] = array(
            'label' => __('Hide `View cart` link after add product', 'woo-better-usability'),
            'tab' => __('General', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'no',
        );
        $arr['product_max_qty'] = array(
            'label' => __('Max product quantity allowed (blank = disabled)', 'woo-better-usability'),
            'tab' => __('General', 'woo-better-usability'),
            'type' => 'number',
            'default' => '',
            'min' => 1,
            'max' => 100,
        );
        $arr['ajax_timeout'] = array(
            'label' => __('AJAX timeout in milliseconds for the quantity change refresh', 'woocommerce-ajax-cart'),
            'tab' => __('General', 'woocommerce-ajax-cart'),
            'type' => 'number',
            'default' => 800,
            'min' => 1,
            'max' => 5000,
        );
        $arr['checkout_allow_delete'] = array(
            'label' => __('Allow to delete products on checkout page', 'woo-better-usability'),
            'tab' => __('General', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'yes',
        );
        $arr['checkout_allow_change_qty'] = array(
            'label' => __('Allow to change quantity on checkout page', 'woo-better-usability'),
            'tab' => __('General', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'yes',
        );
        $arr['checkout_display_unit_price'] = array(
            'label' => __('Display unit price on product name', 'woo-better-usability'),
            'tab' => __('General', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'yes',
            'depends_on' => 'checkout_allow_change_qty',
        );

        // Shop tab
        $arr['enable_quantity_on_shop'] = array(
            'label' => __('Allow to change product quantity on shop page (simple products)', 'woo-better-usability'),
            'tab' => __('Shop', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'yes',
        );
        $arr['qty_as_select_shop'] = array(
            'label' => __('Show quantity as select instead of numeric field', 'woo-better-usability'),
            'tab' => __('Shop', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'no',
            'depends_on' => 'enable_quantity_on_shop',
        );
        $arr['show_show_quantity_buttons'] = array(
            'label' => __('Show -/+ buttons around item quantity', 'woo-better-usability'),
            'tab' => __('Shop', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'yes',
            'depends_on' => 'enable_quantity_on_shop',
        );
        $arr['enable_direct_checkout'] = array(
            'label' => __('Go to checkout directly instead of cart', 'woo-better-usability'),
            'tab' => __('Shop', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'no',
        );
        $arr['direct_checkout_add_cart_text'] = array(
            'label' => __('Text to replace the `Add to cart` button', 'woo-better-usability'),
            'tab' => __('Shop', 'woo-better-usability'),
            'type' => 'text',
            'default' => __('Buy now', 'woo-better-usability'),
            'depends_on' => 'enable_direct_checkout',
        );
        $arr['replace_view_cart_text'] = array(
            'label' => __('Text to replace the `View cart` link', 'woo-better-usability'),
            'tab' => __('Shop', 'woo-better-usability'),
            'type' => 'text',
            'default' => __('View checkout', 'woo-better-usability'),
            'depends_on' => 'enable_direct_checkout',
        );
        $arr['shop_change_products_per_show'] = array(
            'label' => __('Override the default number of products per row', 'woo-better-usability'),
            'tab' => __('Shop', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'no',
        );
        $arr['shop_products_per_row'] = array(
            'label' => __('Number of products to display per row', 'woo-better-usability'),
            'tab' => __('Shop', 'woo-better-usability'),
            'type' => 'number',
            'default' => 4,
            'min' => 1,
            'max' => 15,
            'depends_on' => 'shop_change_products_per_show',
        );
        $arr['hide_shop_paginator'] = array(
            'label' => __('Hide product pagination', 'woo-better-usability'),
            'tab' => __('Shop', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'no',
        );
        $arr['hide_shop_sorting'] = array(
            'label' => __('Hide `Default sorting` select box', 'woo-better-usability'),
            'tab' => __('Shop', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'no',
        );
        $arr['hide_addtocart_button'] = array(
            'label' => __('Hide `Add to cart` button', 'woo-better-usability'),
            'tab' => __('Shop', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'no',
        );

        // Product tab
        $arr['qty_as_select_product'] = array(
            'label' => __('Show item quantity as select instead of numeric field', 'woo-better-usability'),
            'tab' => __('Product', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'no',
        );
        $arr['product_ajax_add_to_cart'] = array(
            'label' => __('Enable AJAX add to cart on product page (simple products only)', 'woo-better-usability'),
            'tab' => __('Product', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'yes',
        );
        $arr['product_hide_price_variable'] = array(
            'label' => __('Hide price range for variable products', 'woo-better-usability'),
            'tab' => __('Product', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'no',
        );
        $arr['product_hide_price_grouped'] = array(
            'label' => __('Hide price range for grouped products', 'woo-better-usability'),
            'tab' => __('Product', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'no',
        );
        $arr['product_hide_quantity'] = array(
            'label' => __('Hide quantity input on product page', 'woo-better-usability'),
            'tab' => __('Product', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'no',
        );

        // Cart tab
        $arr['enable_auto_update_cart'] = array(
            'label' => __('Update cart/prices automatically when change quantity', 'woo-better-usability'),
            'tab' => __('Cart', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'yes',
        );
        $arr['cart_ajax_method'] = array(
            'label' => __('Technical method to update cart prices', 'woo-better-usability'),
            'tab' => __('Cart', 'woo-better-usability'),
            'type' => 'select',
            'options' => array(
                'simulate_update_button' => __('Simulate click on "Update cart" button', 'woo-better-usability'),
                'make_specific_ajax' => __('Run custom AJAX', 'woo-better-usability'),
            ),
            'default' => 'make_specific_ajax',
            'depends_on' => 'enable_auto_update_cart',
        );
        $arr['cart_updating_display'] = array(
            'label' => __('Display text while updating cart automatically', 'woo-better-usability'),
            'tab' => __('Cart', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'yes',
        );
        $arr['cart_updating_location'] = array(
            'label' => __('Where to display the `Updating` text', 'woo-better-usability'),
            'tab' => __('Cart', 'woo-better-usability'),
            'type' => 'select',
            'options' => array(
                'checkout_btn' => __('Proceed to checkout button', 'woo-better-usability'),
                'update_cart_btn' => __('Update cart button', 'woo-better-usability'),
            ),
            'default' => 'checkout_btn',
            'depends_on' => 'cart_updating_display',
        );
        $arr['cart_updating_text'] = array(
            'label' => __('Text to display in Update cart button while loading', 'woo-better-usability'),
            'tab' => __('Cart', 'woo-better-usability'),
            'type' => 'text',
            'default' => __('Updating...', 'woo-better-usability'),
            'depends_on' => 'cart_updating_display',
        );
        $arr['show_qty_buttons'] = array(
            'label' => __('Show -/+ buttons around item quantity', 'woo-better-usability'),
            'tab' => __('Cart', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'no',
        );
        $arr['qty_buttons_lock_input'] = array(
            'label' => __('Lock number input forcing the use of +/- buttons', 'woo-better-usability'),
            'tab' => __('Cart', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'no',
            'depends_on' => 'show_qty_buttons',
        );
        $arr['qty_as_select_cart'] = array(
            'label' => __('Show item quantity as select instead of numeric field', 'woo-better-usability'),
            'tab' => __('Cart', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'no',
        );
        $arr['qty_select_items'] = array(
            'label' => __('Items to show on select', 'woo-better-usability'),
            'tab' => __('Cart', 'woo-better-usability'),
            'type' => 'number',
            'default' => 5,
            'depends_on' => 'qty_as_select_cart',
            'min' => 1,
            'max' => 50,
        );
        $arr['confirmation_zero_qty'] = array(
            'label' => __('Show user confirmation when change item quantity to zero', 'woo-better-usability'),
            'tab' => __('Cart', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'yes',
        );
        $arr['zero_qty_confirmation_text'] = array(
            'label' => __('Confirmation text', 'woo-better-usability'),
            'tab' => __('Cart', 'woo-better-usability'),
            'type' => 'text',
            'default' => __('Are you sure you want to remove this item from cart?', 'woo-better-usability'),
            'depends_on' => 'confirmation_zero_qty',
            'size' => 50,
        );
        $arr['cart_hide_quantity'] = array(
            'label' => __('Hide quantity input on cart page', 'woo-better-usability'),
            'tab' => __('Cart', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'no',
        );
        $arr['cart_hide_update'] = array(
            'label' => __('Hide the `Update cart` button', 'woo-better-usability'),
            'tab' => __('Cart', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'no',
        );
        $arr['cart_fix_enter_key'] = array(
            'label' => __('Fix layout break when Enter key is pressed on cart', 'woo-better-usability'),
            'tab' => __('Cart', 'woo-better-usability'),
            'type' => 'checkbox',
            'default' => 'no',
        );

        return $arr;
    }

    function plugin_init() {

        // common lib init
        include_once( dirname(__FILE__) . '/../common/MHCommon.php' );
        MHCommon::initializeV2(
            'woo-better-usability',
            'wbu',
            WBU_BASE_FILE,
            __('WooCommerce Better Usability', 'woo-better-usability')
        );

        // if ( function_exists('is_shop') &&
        //      wbu()->is_shop_loop() &&
        //      wbu()->option('optimize_cart_other_pages') == 'yes' ) {
        //     wbu()->optimize_cart_on_shop();
        // }
    }

    function shop_quantity_area_args($args) {
        if ( wbu()->option('hide_addtocart_button') === 'yes' ) {
            $args['class'] .= ' wbu-hide hide';
        }

        return $args;
    }

    // this is custom code to cart page ajax work in pages like "Woocommerce Shop page", using the common WC shortcode [cart]
    function optimize_cart_on_shop() {
        wbu()->enqueue_cart_js();
    }

    function enqueue_cart_js() {
        $path = 'assets/js/frontend/cart.js';
        $src = str_replace( array( 'http:', 'https:' ), '', plugins_url( $path, WC_PLUGIN_FILE ) );

        $deps = array( 'jquery' );
        wp_enqueue_script( 'wc-cart', $src, $deps, WC_VERSION, true );
    }

    function js_uri() {
        $file = ( defined('WP_DEBUG') && ( WP_DEBUG === true ) ) ? 'assets/wbulite.js' : 'assets/wbulite.min.js';
        return apply_filters('wbu_js_uri', plugins_url($file, WBU_ROOT_FILE));
    }

    function css_uri() {
        return apply_filters('wbu_css_uri', plugins_url('assets/wbulite.css', WBU_ROOT_FILE));
    }

    function enqueue_assets() {
        // enqueue assets
        wp_enqueue_script('wbulite', wbu()->js_uri(), array('jquery'));
        wp_enqueue_style('wbulite', wbu()->css_uri());

        wp_localize_script('wbulite', 'wbuSettings', $this->get_all_options());
        wp_localize_script('wbulite', 'wbuInfo', array(
            'isCart' => is_cart(),
            'isShop' => wbu()->is_shop_loop(),
            'isSingleProduct' => is_product(),
            'isCheckout' => is_checkout(),
            'ajaxUrl' => get_admin_url() . 'admin-ajax.php',
            'quantityLabel' => __('Quantity', 'woo-better-usability'),
        ));
    }

    function get_all_options() {
        $allOptions = apply_filters('mh_wbu_all_options', array());

        // customer request to disable some functions when using Uni CPO plugin
        // and the Uni CPO is enabled for the current product
        // if ( $this->is_unicpo_enabled_for_product() ) {
        //     // this is premium version option
        //     if ( !empty($allOptions['enable_auto_update_product']) ) {
        //         $allOptions['enable_auto_update_product'] = 'no';
        //     }
        // }

        return $allOptions;
    }

    // function is_unicpo_enabled_for_product() {
    //     if ( is_product() ) {
    //         global $post;

    //         if ( !empty($post->ID) ) {
    //             return ( get_post_meta( $post->ID, '_cpo_enable', true ) == 'on' );
    //         }
    //     }

    //     return false;
    // }

    function is_shop_loop() {
        return ( is_shop() || is_product_category() || apply_filters('wbu_is_shop_loop', array()) );
    }

    // experimental function used with new woocommerce in cases when the old is_shop_loop() not works with all use cases, eg.:
    // when in page: "[url]/shop/?add-to-cart=[id]" is_shop_loop() not returns TRUE anymore
    // the idea is to change this as oficial in future
    // function is_shop_loop_new() {
    //     return wbu()->is_shop_loop() || !empty($_REQUEST['add-to-cart']);
    // }

    function enqueue_scripts() {
        if ( !class_exists('WooCommerce') ) {
            return;
        }

        wbu()->enqueue_assets();
    }

    function template_path($name) {
        return apply_filters('wbu_get_template', WBU_ROOT_DIR . '/templates/' . $name);
    }
    
    function get_template($name, $args = array()) {
        if ( !empty($args) ) {
            extract($args);
        }
        
        ob_start();
        include $this->template_path($name);
        
        return ob_get_clean();
    }

    function option($name) {
        return apply_filters('mh_wbu_setting_value', $name);
    }
    
    function select_max_quantity($product = null) {
        $productMaxQty = wbu()->option('product_max_qty');
        $maxQty = ( $productMaxQty > 0 ? $productMaxQty : wbu()->option('qty_select_items') );

        if ( !empty($product) && !$product->backorders_allowed() ) {
            $stockQty = $product->get_stock_quantity();

            if ( $stockQty > 0 && $stockQty < $maxQty ) {
                return $stockQty;
            }
        }

        return $maxQty;
    }

    function filter_add_cart_message($message) {
        if ( wbu()->option('hide_viewcart_link') === 'yes' ) {
            $msgDelete = sprintf( '<a href="%s" class="button wc-forward">%s</a>', esc_url( wc_get_page_permalink( 'cart' ) ), esc_html__( 'View cart', 'woocommerce' ));

            $message = str_replace($msgDelete, '', $message);
        }

        // when configured to hide added messages, then erase message to woocommerce not display
        if ( wbu()->option('hide_addedtocart_msg') === 'yes' ) {
            $message = null;
        }

        return $message;
    }
    
    static function boot() {
        define('WBU_ROOT_DIR', plugin_dir_path( dirname(__FILE__)) );
        define('WBU_ROOT_FILE', dirname(dirname(__FILE__)) . '/' . basename(WBU_PLUGIN) );

        include_once( WBU_ROOT_DIR . 'includes/class-wbu-cart.php' );
        include_once( WBU_ROOT_DIR . 'includes/class-wbu-checkout.php' );
        include_once( WBU_ROOT_DIR . 'includes/class-wbu-shop.php' );
        include_once( WBU_ROOT_DIR . 'includes/class-wbu-product.php' );
        
        if ( !function_exists('wbu') ) {
            /**
             * @return WBU
             */
            function wbu() {
                static $instance = null;

                if ( !$instance ) {
                    $instance = new WBU();
                }

                return $instance;
            }
        }
        
        wbu()->init_classes();
        wbu()->init_hooks();
    }
}
}

