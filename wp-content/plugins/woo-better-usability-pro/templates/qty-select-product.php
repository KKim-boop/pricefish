<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php global $product; ?>
<div class="quantity">
    <select name="<?php echo esc_attr( $input_name ); ?>"
            class="input-text qty text">
        <?php for ( $i=0; $i <= wbu()->select_max_quantity($product); $i++ ): ?>
            <option <?php if ( esc_attr( $input_value ) == $i ): ?>selected="selected"<?php endif; ?>
                    value="<?php echo $i; ?>">
                <?php echo $i; ?>
            </option>
        <?php endfor; ?>
    </select>
</div>
