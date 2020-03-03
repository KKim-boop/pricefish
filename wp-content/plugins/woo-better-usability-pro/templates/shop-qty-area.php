<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php if ( $allowChangeQty ): ?>
    <form action="<?= esc_url( $product->add_to_cart_url() ) ?>" class="cart" method="post" enctype="multipart/form-data" onsubmit="return false;">
        <?php echo $quantityField; ?>
        <?php echo sprintf( '<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
                esc_url( $product->add_to_cart_url() ),
                esc_attr( $initialQuantity ),
                esc_attr( $class ),
                'data-product_id="'.$product->get_id().'"',
                esc_html( $product->add_to_cart_text() )
            ); ?>
    </form>
<?php else: ?>
    <?php echo $quantityField; ?>
<?php endif; ?>
