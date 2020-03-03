<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php if ( $useSelectField && $product->is_type('simple') ): ?>
    <div class="quantity">
        <select class="input-text qty text">
            <?php for ( $i=0; $i <= wbu()->select_max_quantity($product); $i++ ): ?>
                <option <?php if ( $i == $inputValue ): ?>selected="selected"<?php endif; ?>
                        value="<?php echo $i; ?>">
                    <?php echo $i; ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>
<?php else: ?>
    <?php echo woocommerce_quantity_input(array(
            'min_value' => 0,
            'max_value' => $product->get_stock_quantity(),
            'input_value' => $inputValue,
        ), $product, false ); ?>
<?php endif; ?>
