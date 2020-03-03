<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package pizzaro
 */

?>

		</div><!-- .col-full -->
	</div><!-- #content -->

	<!--<?php do_action( 'pizzaro_before_footer_v2' ); ?>-->

	<footer id="colophon" class="site-footer footer-v2" role="contentinfo">
		<div class="col-full">

			<div class="footer-row row vertical-align">		
				<div class="footer-store-info">
						
					<!--<h5>Opening times</h5>

					<ul class="store-timings">
					<li>
				<span class="store-timing-label"><b>Monday</b></span>
				<span class="store-timing-value">5:00pm – 9:00pm</span>
			</li>
			<li>

				<span class="store-timing-label"><b>Tues - Saturday</b></span>
				<span class="store-timing-value">12:00pm – 1:45pm &amp; 5:00pm – 9:00pm</span>
			</li>
				
					<li>
				<span class="store-timing-label"><b>Sundays</b></span>
				<span class="store-timing-value">Closed</span>
			</li>
				</ul>-->
				<!-- /.store-timings -->


				<div class="footer-logo">
						<a href="<?php bloginfo('url'); ?>" class="custom-logo-link" rel="home" itemprop="url">
							<img  src="<?php bloginfo('url'); ?>/wp-content/uploads/2018/01/medium-logo.png" class="custom-logo" alt="Price's Fish and Chips" >
						</a>

							<img class="img-responsve center-block" src="https://pricesfishandchips.co.uk/wp-content/uploads/2018/07/foodhygeine.png" />
					</div>	
					
					</div><!-- /.footer-store-info -->


		


					<!-- FOOTER CONTACT -->
					<div class="footer-contact-form">
				<!--<div class="contact-form">
					<h3 class="contact-form-title">Say hi to Prices Fish and Chips</h3>
							
<?php echo do_shortcode('[contact-form-7 id="958" title="Footer Contact"]') ?>

						</div>-->

						<h5>About us</h5>
						<p>We farm locally and it is important to us that the food we serve is good quality and sustainably produced from an identifiable source. We are proud to offer sustainably sourced Cod, Haddock &amp; Plaice. As well as beef and sausages from Crow farm Ringwood.</p>


			</div>	

			
			
		<!--INFO -->
		<div class="footer-contact-info">
			<ul class="address">
				<h5>Where are we</h5>
				<li>
					<span class="address-text">38-40 Christchurch Rd, Ringwood BH24 1DN</span>
				</li>
				<h5>Small Print</h5>
				<li>
					<a class="gjfooter" href="<?php bloginfo('url'); ?>/terms-and-conditions/">Terms and Conditions</a>
				</li>
				<h5>Phone Us</h5>
				<li>
					<span class="address-text">01425 480290</span>
				</li>
				<h5>Social</h5>
				<li>
					<a class="gjbi" href="https://www.facebook.com/PricesFishandChips/"><i class="fa fa-facebook" aria-hidden="true"></i></a>
					
					<a class="gjbi" href="https://www.instagram.com/prices_fish_and_chips_ringwood/"><i class="fa fa-instagram" aria-hidden="true"></i></a>



				</li>
			</ul>
		</div>
		<!-- END INFO-->

		<style type="text/css">

		.address-text{
			color: #ffffff;
		}

		.gjfooter{color: #ffffff;}
		.gjbi {color: #ffffff;}

		/*menu stuff*/
		.menu-item{
			padding: 0px;
			margin: 0px;
			font-size: 12px;
		}

		/*header stuff*/
		@media (min-width: 992px){
			 .header-v1 .site-branding {
   			 width: 8%;
			}
		}

		.header-v1 .site-header-cart-v2 .cart-content>a{
			border-radius: 10px;
			padding: 10px;
		}

		.header-phone-numbers .phone-number{
			font-size: 1.2em;
			text-align: center;
		}

		@media (max-width: 480px){
			.site-header .custom-logo-link img {
				max-width: 80px;
		}}

		@media (max-width: 991px){
			.site-header .custom-logo-link img {
				max-width: 80px;
		}}

			/*cart - checkout - styling*/
		.page-id-9 .quantity .input-text{
			border-radius: 5px !important;
			width: 4em !important;
		}

		.page-id-9 .form-row.woocommerce-validated input.input-text{
			border-radius: 5px !important;
		}

		.page-id-9 .woocommerce-checkout .input-text{
			border-radius: 5px;
		}

		.page-id-9 .woocommerce-shipping-totals th{
			width: 50%;
		}


		</style>





		</div><!-- /.footer-row -->		




		<div class="pizzaro-handheld-footer-bar">
			<ul class="columns-3">
				<li class="my-account">
					<a href="<?php bloginfo('url'); ?>/my-account/">My Account</a>					
				</li>
				<li class="searchl">
						<a href="<?php bloginfo('url'); ?>/shop">Shop</a>			

				</li>
				<style type="text/css">
				
				.searchl>a:before{content: "\f07a";}

				</style>
				<li class="cart">
					<a class="footer-cart-contents" href="<?php bloginfo('url'); ?>/cart" title="View your shopping cart">
				<span class="count">0</span>
			</a>
		
							</li>
							</ul>
		</div>
		
		</div><!-- .col-full -->
	</footer>

	<?php do_action( 'pizzaro_after_footer_v2' ); ?>

</div><!-- #page -->

<?php wp_footer(); ?>

<!--BX Slider-->
<script>



   
    jQuery(document).ready(function($){
     $('.slider').bxSlider({
     	pager: false
     });

    });

    jQuery( document ).ajaxComplete(function() {
			

			if( jQuery('body.woocommerce-checkout').length){

			var sopiotns = 	document.getElementById("shipping_method").getElementsByTagName("li").length;
			console.log(sopiotns);

			if (sopiotns > 1){

			

				if(document.getElementById('shipping_method_0_flat_rate1').checked) {
	  		console.log("delivery selected");
	 		var elem = document.getElementById("place_order");
			 //console.log(elem)
	   		elem.innerHTML = "Place Order for Delivery";

		}
		else if(document.getElementById('shipping_method_0_local_pickup2').checked) {
	  		console.log("pickup selected");
	  		var elem = document.getElementById("place_order");
	  		elem.innerHTML  = "Place Order for Collection";
		} else if (document.getElementById('shipping_method_0_free_shipping5').checked){
			console.log("free delivery selected");
	  		var elem = document.getElementById("place_order");
	  		elem.innerHTML  = "Place Order with Free Delivery";


		}
		//end greater than 1
	} else {
		var elem = document.getElementById("place_order");
		elem.innerHTML  = "Place Order for Collection";
	}

			}//end check chekcout


	});

  </script>
<!--End BX-->  

</body>
</html>