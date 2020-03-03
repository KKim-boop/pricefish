<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php if ( $allowDelete == 'yes' ): ?>
    <a href="<?= esc_url( wc_get_cart_remove_url( $cart_item_key ) ) ?>"
       class="remove"
       title="<?= __( 'Remove this item', 'woocommerce' ) ?>"
       data-product_id="<?= esc_attr( $product_id ) ?>"
       data-product_sku="<?= esc_attr( $product->get_sku() ) ?>">&times;
    </a>
	&nbsp;
<?php endif; ?>

<span class="product_name">
    <?= $product_title ?>
</span>

<?php if ( $allowChangeQty == 'yes' && $displayUnitPrice == 'yes' ): ?>
    <span class="product_price">
        <?= wc_price($cart_item['data']->get_price()) ?>
    </span>
<?php endif; ?>

<?php if ( $allowChangeQty == 'yes' ): ?>
    <?php if ( $product->is_sold_individually() ): ?>
      1 <input type="hidden" name="cart[<?= $cart_item_key ?>][qty]" value="1" />
    <?php else: ?>
        <?php
            echo wbu()->get_template('checkout-product-qty.php', array(
                'product' => $product,
                'cart_item_key' => $cart_item_key,
                'cart_item' => $cart_item,
            ));
        ?>
    <?php endif; ?>
<?php endif; ?>
