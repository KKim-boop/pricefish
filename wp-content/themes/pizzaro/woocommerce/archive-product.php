<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header(); ?>



<!--styles-->
<style type="text/css">


.single-product div.product form.cart .button {
    /* clear: left; */
    /* float: right; */
    float: left;
    width: 100%;
    margin-top: 10px;
    padding: 20px;
}

.single-product div.product form.cart .ywapo_group_container_radio {
    padding: 15px;
    margin-bottom: 0;
}

.ywapo_input_container_radio:not(.pz-radio-default) label span {
    width: 100%;
    display: inline-block;
    line-height: 12px;
    padding: 12px 0;
    position: relative;
    z-index: 1;
}

.ywapo_input_container ywapo_input_container_radio {min-wdith: 60px;}

.ywapo_input_container_radio:not(.pz-radio-default) label::before {
    content: " ";
    width: 70px;
    border-radius: 100%;
    height: 70px;
    border: 2px solid #f9f1db;
    position: absolute;
    transform: translate(-50%,-50%);
    top: 50%;
    left: 50%;
    transition: all .2s ease-in-out 0s;
}


.lukecustomshoprow{padding-bottom: 10px;}
h2 {color: #bba22a;}
.affix{top: 20px;}	
.lukecustomshoprow{padding-bottom: 10px;}
.button {border-radius: 5px; padding: 3px; }
.gjcart{
		background-color: #f5f5f5;
	padding: 15px;
	padding-bottom: 15px;
border-radius: 20px;}
.lukecustomshoprow{padding-bottom: 10px;}

.pizzaro-sorting{display: none;}
.alertgjorderinfo {border: 5px solid red; padding: 20px; margin-bottom: 10px;}

.woocommerce-mini-cart-item .wp-post-image{
	display: none;
}

.wbu-quantity .qty-btn label{
	display: none;
}
.columns-3 ul.product-loop-categories>li, .columns-3 ul.products>li {
    width: 100% !important;
    list-style:none !important;
}
.wbu-quantity .input-text{
	border-radius: 0px !important;
	width: 80% !important;
    padding: 0px !important;
    margin: 0px !important;
    float: left !important;
	margin-right: 5px !important;
}

.wbu-btn-sub{
	float: left !important;
	text-align: center;
	width: 10%;
	margin-right: 5px;
	margin-top: 4px;
}

.wbu-btn-inc{
	float: left !important;
	text-align: center;
	width: 10%;
	margin-top: 4px;
	margin-right: 3px;
}

a.remove:before{
	color: red !important;
}
.mini_cart_item a{
font-size: 13px;
margin-bottom: 10px;
}

.add_to_cart_button::before{
	display: none;

}

.add_to_cart_button::after{
	display: none;

}

.mini_cart_item .variation{
	display: none;

}

.added_to_cart{
margin-top: 10px;
    border-radius: 5px;
    padding: 5px;
    background-color: green;
}

.outofstock{
	background-color: red !important;
}

}


</style>


<!--Testing-->

<!-- call function from plugin-->


<!--end call function from another plugin-->

<!--Getting date time-->
<?php 

	date_default_timezone_set('Europe/London');
	$paymentDate = date('H:i:s');


	$isOn = get_option("shopOn");

	if ($isOn == 1){
		$open = 1 ?>

		<!--<div class="row gjorderinfo">
	<div class="col-md-12">
		<p>Online Shop Open</p>
		
	</div>
</div>-->
	<?php } else { 

		$open = 0;
		?>
	   <div class="row alertgjorderinfo">
	<div class="col-md-12">
		
		
		<p>Online Shop Closed
			</p>

	</div>
</div>

<?php    } ?>



<?php


    

    
    $paymentDate=date('H:i:s', strtotime($paymentDate));;
    //echo $paymentDate; // echos today! 
    //$contractDateBegin = date('H:i:s', strtotime("16:15:00"));
    //$contractDateEnd = date('H:i:s', strtotime("20:30:00"));



    $contractDateBegin = date('H:i:s', strtotime("8:00:00"));
    $contractDateEnd = date('H:i:s', strtotime("20:30:00"));

    //test
    //$contractDateBegin = date('H:i:s', strtotime("09:15:00"));

	//get todays day as an integer value
    $whatDay = date('w');



    //Our YYYY-MM-DD date string.
	$date = date('H:i:s');
	

	
 
//Get the day of the week using PHP's date function.
	$dayOfWeek = date("l", strtotime($date));
//0 is sunday
//1 is monday
//2 is tuesday
//3 is wednesday
//4 is thurday
//5 is friday
//6 is saturday

////////Testing section///////////
//Our YYYY-MM-DD date string
//$datetwo = "2019-03-16";
//$dayOfWeektwo = date("w", strtotime($datetwo));



//////////end testing////////

switch ($whatDay) {
    case 0:
	$contractDateEnd = date('H:i:s', strtotime("19:30:00"));
        break;
    case 1:
	$contractDateEnd = date('H:i:s', strtotime("19:30:00"));
        break;
    case 2;
	$contractDateEnd = date('H:i:s', strtotime("20:30:00"));
		break;
	case 3;
	$contractDateEnd = date('H:i:s', strtotime("20:30:00"));
		break;
	case 4;
	$contractDateEnd = date('H:i:s', strtotime("20:30:00"));
        break;
    case 5;
	$contractDateEnd = date('H:i:s', strtotime("20:30:00"));
		break;
	case 6;
	$contractDateEnd = date('H:i:s', strtotime("20:30:00"));
        break;
}

 
 	/*if ( $whatDay == 1){
 		$contractDateEnd = date('H:i:s', strtotime("22:30:00"));
 	}

 	if ( $whatDay == 4){
 		$contractDateEnd = date('H:i:s', strtotime("22:30:00"));
 	}
 	if ( $whatDay == 5){
 		$contractDateEnd = date('H:i:s', strtotime("22:30:00"));
 	}
 	if ( $whatDay == 6){
 		$contractDateEnd = date('H:i:s', strtotime("22:30:00"));
 	}*/
	 //$paymentDate = date('H:i:s', strtotime("17:16:00"));

    if (
    	($paymentDate > $contractDateBegin) && 
    	($paymentDate < $contractDateEnd) 
    	//&& ($whatDay > 0 )
    	) 

    { 

    	//$open = 1; ?>

    		<div class="row gjorderinfo">
	<div class="col-md-12">


		<p>
			
		<?php echo get_option( 'storeWide' ); ?>
		
		<!--We're still on the hunt for a delivery driver so unfortunately we won't be able to offer our delivery service until further notice. Sorry for the inconvenience you can still order from 4pm for Collection in the meantime. £8 minimum order. Remember to put down your mobile number for a text on completion of your order.-->

			<!--<b>Note: We won't be doing delivery this week. Delivery option will be back Wednesday the 3rd July, this is due to staff holidays. Sorry for the inconvenience. You can still order online for Collection as usual from 4pm.</b>-->

			






		</p>
		
	</div>
</div>



    <?php } else { $open = 0 ?>


   <div class="row alertgjorderinfo">
	<div class="col-md-12">
		
	
		
		<p>Please note we only accept online orders today between <?php echo date("g:i a", strtotime($contractDateBegin)); ?>  and <?php echo date("g:i a", strtotime($contractDateEnd)); ?>.<br />The current time is <b>
			<?php echo date("g:i a", strtotime($paymentDate)); ?>
			<?php// echo "Online order is open from =".$contractDateBegin; ?>
			<?php //echo "Online ordering closes at =".$contractDateEnd; ?>
			<?php 
			//echo "day=".$whatDay; 
			//echo "date=".$date;
			//echo "dayoftheweek=".$dayOfWeektwo;
			
					

			 ?>.</b><br />

			

			 <!--<b>Note: We won't be doing delivery this week. Delivery option will be back Wednesday the 3rd July, this is due to staff holidays. Sorry for the inconvenience. You can still order online for Collection as usual from 4pm.</b>-->



			</p>

	</div>
</div>


   <?php  }

    	
    	?>


<div class="row">
	<div class="col-md-12">
		<p>You can find our allergen information here -> <a href="https://www.pricesfishandchips.co.uk/wp-content/uploads/2018/10/Prices-ALLERGEN-documentPDF.pdf">Allergen info</a></p>
	</div>

</div>







<div class="row">


	<!--start cart-->
	<div class="col-md-3 col-md-push-9">


					
		<div class="gjcart" id="gjcarttop">
		
		<?php dynamic_sidebar( 'sidebar' ); ?> 
		</div>
       

	</div><!--end cart-->


	<!--start nav-->
	<div class="col-md-3 col-md-pull-3">
		
		<!--<div <data-spy="affix" data-offset-top="205">-->
		<div>



			<div id="nav_menu-2" class="widget widget_nav_menu">
				<div class="menu-shop-container">
					<ul id="menu-shop" class="menu">
						<li id="menu-item-940" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-940">
							<a href="#Fish">Fish</a>
						</li>
						<li id="menu-item-940" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-940">
							<a href="#meats-and-pies">Meat and Pies</a>
						</li>
						<li id="menu-item-905" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-905">
							<a href="#Burgers">Burgers</a>
						</li>
						<li id="menu-item-941" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-941">
							<a href="#Sides">Sides</a>
						</li>
						<li id="menu-item-937" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-937">
							<a href="#Chips">Chips</a>
						</li>
						<li id="menu-item-941" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-941">
							<a href="#meal-deals">Meal Deals</a>
						</li>
						<li id="menu-item-941" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-941">
							<a href="#kids-meals">Kids Meals</a>
						</li>
						<li id="menu-item-941" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-941">
							<a href="#sauces-and-pickles">Sauces/Pickles</a>
						</li>
						<li id="menu-item-941" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-941">
							<a href="#specials">Specials</a>
						</li>
						
						<li id="menu-item-939" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-939">
							<a href="#Drinks">Drinks</a>
						</li>

						
						<!--<li id="menu-item-942" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-942">
							<a href="#sa">Small Appetite</a>
						</li>
						<li id="menu-item-938" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-938">
							<a href="#ci">Classic Items</a>
						</li>
						<li id="menu-item-905" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-905">
							<a href="#Burgers">Burgers</a>
						</li>
						<li id="menu-item-939" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-939">
							<a href="#Drinks">Drinks</a>
						</li>-->
						
						<!--<li id="menu-item-941" class="menu-item menu-item-type-taxonomy menu-item-object-product_cat menu-item-941">
							<a href="#desserts">Desserts</a>
						</li>
						-->
				</ul>
			</div><!--end menu shop-->
		</div><!--end nav-->
		
	

	</div>
	</div>
	<!--end md-3 end nav-->

	<div class="col-md-6 col-md-pull-3">


		
		
				<!--FISH AREA-->

				<?php 
					for ($x = 0; $x <= 9; $x++) {

						if ($x == 0){
							$prodcat = 'Fish';
						} else if ($x == 1){
							$prodcat = 'meats-and-pies';
						} else if ($x == 2){
							$prodcat = 'Burgers';
						} else if ($x == 3){
							$prodcat = 'Sides';
						} else if ($x == 4){
							$prodcat = 'Chips';
						} else if ($x == 5){
							$prodcat = 'meal-deals';
						} else if ($x == 6){
							$prodcat = 'kids-meals';
						} else if ($x == 7){
							$prodcat = 'sauces-and-pickles';
						} else if ($x == 8){
							$prodcat = 'specials';
						} else if ($x == 9){
							$prodcat = 'Drinks';
						}


						?>

						

						<h2 id="<?php 
						if ($prodcat === 'Classic Items'){
							echo 'ci';
						} else if ($prodcat === 'Small Appetite'){
							echo 'sa';
						} else {
							echo $prodcat;
						}
				
						?>">
							<?php 
							if ($prodcat === 'desserts')
							{
								echo 'Desserts';
							} else if ($prodcat === 'specials') {
								echo 'Specials';
							} else if ($prodcat === 'kids-meals') {
								echo 'Kids Meals';
							} else if ($prodcat === 'meal-deals') {
								echo 'Meal Deals';
							} else if ($prodcat === 'meats-and-pies'){
								echo 'Meats and Pies';

							} else if ($prodcat === 'sauces-and-pickles'){
								echo 'Sauces and Pickles';
							} else {
								echo $prodcat ;
							}
		
							?>
						
						</h2>
							<ul class="products">
								<?php
									$Fargs = array( 
									'post_type' => 'product', 
									'posts_per_page' => 100, 
									'product_cat' => $prodcat,
									'orderby' => 'menu_order',
									'order' => 'ASC'
									);	

									$F_products = new WP_Query( $Fargs );

									if ( $F_products->have_posts() ) : 
									while ( $F_products->have_posts() ) : $F_products->the_post(); 
									$y = $product->get_id();
									$quant = $product->get_stock_quantity();
									$availability = $product->get_availability();
								?>

						<li class="">
							<div class="product-content-wrapper row lukecustomshoprow">
								<div class="col-md-4">
									<p>
										<?php the_title(); 
												//echo $availability['availability']; 
										?>
									</p>
								</div>
								<div class="col-md-4">
									<?php woocommerce_template_loop_price(); ?>	
								</div>

								<div class="col-md-4">	
								<?php if ($open == 1){
										if ($prodcat === 'Burgers' || $prodcat === 'meal-deals' || $y == '1069' || $prodcat === 'kids-meals' ||
											$y == '1020' || 
											$y == '1021' || 
											$y == '1064' || 
											$y == '1062' ||
											$y == '1022' ||
											$y == '1068' || $y == '1025' || $y == '1029' ||  $y == '3746' || $y == '3731' || $y == '973'){ ?>
											<a href="#" class="button yith-wcqv-button" data-product_id="<?php  echo ($y) ?> ">Select options</a>

										<?php } else if ($availability['availability'] === 'Out of stock'){ ?>
											<a href="javascript:void(0)" data-quantity="1" class="button outofstock product_type_simple add_to_cart_button ajax_add_to_cart added" data-product_id="" data-product_sku="" aria-label="Out of stock" rel="nofollow">Out of stock</a>

										<?php }
										
										//for out of stock//
										else { ?>
											
											<a href="pricefish/shop/?add-to-cart=<?php echo $y ?>" data-quantity="1" class="button product_type_simple add_to_cart_button ajax_add_to_cart added" data-product_id="<?php echo $y ?>" data-product_sku="" aria-label="Add to cart" rel="nofollow">Add to cart</a>
										

										<?php }

								} ?>
								
								</div>		
							</div>
							<hr>
						</li>

							<?php
									endwhile; // end of the loop. 
									endif; // end of the if statement. 
												
							?>

						</ul>

							<?php
								}
				
							?>
					
						</div>
					</div>
					</div>

<script type="text/javascript">

//add smooth scrolling
jQuery('a[href^="#"]').on('click', function(event) {
    var target = jQuery(this.getAttribute('href'));
    if( target.length ) {
        event.preventDefault();
        jQuery('html, body').stop().animate({
            scrollTop: target.offset().top
        }, 1000);
    }
});


//change behaviour of add to cart button to stop re-direct, naviagte to cart and remove view cart button//
jQuery( document ).ajaxComplete(function() {

	//console.log('stopping fire')
	jQuery('.variation').remove();

	jQuery( ".added_to_cart" ).click(function( event ) {
		//console.log('clicked')
  		event.preventDefault();
		  var elmnt = document.getElementById("gjcarttop");
			elmnt.scrollIntoView();
			jQuery(".added_to_cart").css("display", "none");
		
  
});

});


			//remove element
jQuery(document).ready(function($){
	//console.log('tester');
    

	setTimeout(function(){ 
		jQuery('.variation').remove();
		}, 8000);
});


</script>
	




		

		

<?php get_footer( 'shop' ); ?>





