<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

while ( have_posts() ) : the_post(); ?>

 <div class="product">

	<div id="product-<?php the_ID(); ?>" <?php post_class('product'); ?>>

		
		<div class="summary entry-summary">
			<div class="summary-content">
			
			
			
				<style>
				//pop up styles

				#yith-quick-view-content div.summary{
					width: 100%;
				}
				.ywapo_option_label ywapo_label_position_after{
					wdith: 100%;
				}

				
				</style>

				<script>

				jQuery( document ).ready(function() {
    				console.log( "ready! pop up" ); 
					jQuery('div#ywapo_value_4 input[type=checkbox]').change(function () {
					console.log("clicked me")
					if (jQuery('div#ywapo_value_4 input[type=checkbox]:checked').length > 2) {
				        jQuery(this).prop('checked', false);
				        alert("Please select only two sauces");
				    }
				    				/*if (this.checked) 
					{
      					console.log("Thanks for checking me");
						//uncheck others
						var y = jQuery(this).attr('id');
						jQuery('#ywapo_value_4 input:checked').each(function() {
							var x = jQuery(this).attr('id');
							if ( x === y){
								console.log('remain checked')
							} else {
			
							jQuery('#' + x).prop('checked', false);
							
							}
						});
    				}*/
				});



			});
					</script>

				<?php do_action( 'yith_wcqv_product_summary' ); ?>
			</div>
		</div>

	</div>

</div>

<?php endwhile; // end of the loop.