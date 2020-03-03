<?php
/**
 * The template for displaying the homepage.
 *
 * This page template will display any functions hooked into the `pizzaro_contactpage` action.
 * By default this includes a variety of product displays and the page content itself. To change the order or toggle these components
 * use the Homepage Control plugin.
 * https://wordpress.org/plugins/homepage-control/
 *
 * Template name: Contactpage
 *
 * @package pizzaro
 */

remove_action( 'pizzaro_content_top', 'pizzaro_breadcrumb', 10 );

do_action( 'pizzaro_before_contactpage' );

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main">


		<!--<div class="row">
			<div class="col-md-12">
				<h2 class="contactTitleHead">Contact and Information for Prices Fish and Chips</h2>
			</div>
		</div>-->

			<div class="row">
				<div class="col-md-6">
					<h1 class="contactTitle">Opening times</h1>
						<table class="table">
							<thead class="thead-dark">
								<tr>
								<th scope="col">Day</th>
								<th scope="col">Lunch</th>
								<th scope="col">Evening</th>
								</tr>
							</thead>
							<tbody>
								<tr>
								<th scope="row">Monday</th>
								<td>Closed</td>
								<td>5-8 PM</td>
								</tr>
								<tr>
								<th scope="row">Tuesday</th>
								<td>Closed</td>
								<td>5-9 PM</td>
								</tr>
								<tr>
								<th scope="row">Wednesday</th>
								<td>12 - 1:45 PM</td>
								<td>5-9 PM</td>
								</tr>
								<tr>
								<th scope="row">Thursday</th>
								<td>12 - 1:45 PM</td>
								<td>5-9 PM</td>
								</tr>
								<tr>
								<th scope="row">Friday</th>
								<td>12 - 1:45 PM</td>
								<td>5-9 PM</td>
								</tr>
								<tr>
								<th scope="row">Saturday</th>
								<td>12 - 1:45 PM</td>
								<td>5-9 PM</td>
								</tr>
								<tr>
								<th scope="row">Sunday</th>
								<td>Closed</td>
								<td>5-8 PM</td>
								</tr>
							</tbody>
							</table>

							<h2 class="contactTitle">Where are we</h2>
						<p>38 - 40 Christchurch Rd <br />
							Ringwood <br />
						 Hampshire<br />
						BH24 1DN<br />
						</p>
						<h2 class="contactTitle">Phone</h2>
						CALL 01425 480290

							

				</div>
				<div class="col-md-6">
					


					<h2 class="contactTitle">Car Parking</h2>
					<p>Parking can be hard on our road but did you know after 6pm, 7 days a week, the Blynkbonnie car park BH24 4AX is free to use? It’s off of Christchurch Road. If you’re heading from the direction of the Memorial or Greyfriars you head past our big blue chip shop and take the first left turn just before the Trinity Church/Wesley centre.</p>
						
					<img alt="Prices fish and chips ringwood location" class="img-responsve center-block" src="<?php bloginfo('url'); ?>/wp-content/uploads/2019/05/priceslocationmap.jpg" />

				</div>

			</div>
			<hr>
			<div class="row">
			<div class="col-md-6">
					<h2 class="contactTitle">Orders for large groups and events.</h2>
					<p>- Got a group to feed? Why not let us take the stress away by cooking for you. Be it for a birthday party, retirement home, office meeting, social or sports team, special occasion big or small - we can cater for you.</p>
					<p>- For large outside catering orders please message us on our Facebook page, call us on 01425 480290 or pop in store to discuss your order and any queries you may have. We do offer discounts and freebies to orders over certain amounts.</p>

<p>- Please give us plenty of notice so we can ensure all prep work is done and so we can organise sufficient staff numbers.</p>
<p>- For orders over £70 we will provide wooden forks, serviettes, lemon slices as well as salt and vinegar sachets.</p>
<p>- For orders over £100 we can offer 10% off your meal OR to the amount of 10% of your order we will give you a choice of condiments or sauces EG vinegar/ketchup bottles, Curry sauce Mushy Peas or Gravy on top of your purchase. IE £100 of food plus £10 (10%) worth of sides/condiments of your choice.</p>
<p>- To those ordering from Ringwood Free delivery is also an option Wednesday-Saturday evenings.</p>
<p>- Large orders will need to be paid a minimum 24hours in advance.</p>
					
				</div>
				<div class="col-md-6">
					<h2 class="contactTitle"> FAQ </h2>
				


							<button class="accordion">What evenings do you deliver? </button>
							<div class="panel">
							<p>We only deliver on certain days however for Pre orders over £70 we will deliver within a 4 mile radius any day of the week. Pre orders must be made and paid for 72 hours in advanced.</p>
							</div>

							<button class="accordion">Where do you deliver to?</button>
							<div class="panel">
							<p>We only deliver to within a 3 to 4 mile radius of the shop. Our website works this out if you’re within the catchment area. If you are within the radius the delivery option will pop up at checkout.</p>
							</div>

							<button class="accordion">Do you have gluten free options?</button>
							<div class="panel">
							<p>Unfortunately we do not advertise any gluten free items as we cannot 100% guarantee that they are. Chips are cooked separately but we’d advise if you are Coeliac then you stay clear.</p>
							</div>

							<button class="accordion"> Is there a minimum on card or a charge for using card? </button>
							<div class="panel">
							<p>There is no minimum amount on card and no card handling fee</p>
							</div>

							<button class="accordion">What size is your fish?</button>
							<div class="panel">
							<p>These are all approximates weights in ounces (before cooking): Haddock 8-10oz. Plaice 8-10oz. Small Cod 4.5-5oz. Medium Cod 7.5-8oz. Large Cod 9.5-10ounces</p>
							</div>

							<button class="accordion">What are your chip sizes?</button>
							<div class="panel">
							<p>These are all approximates (cooked weight) in ounces: Small 6oz. Medium 10oz. Large 14oz</p>
							</div>

							<button class="accordion">What frying medium do you use?</button>
							<div class="panel">
							<p>We use Frymax. This is a RSPO Sustainable palm oil.</p>
							</div>


							<button class="accordion">How big are your burgers?</button>
							<div class="panel">
							<p>The single Crow farm burger is 6oz and a double is 12oz.</p>
							</div>

							<button class="accordion">Does your fish have bones in?</button>
							<div class="panel">
							<p>We cut all our Cod in store and try our best to remove all bones however some may slip through the net. Plaice and Haddock will contain bones this is so we don’t compromise the meaty part of the fillets.</p>
							</div>

					<script>
					var acc = document.getElementsByClassName("accordion");
					var i;

					for (i = 0; i < acc.length; i++) {
					acc[i].addEventListener("click", function() {
						this.classList.toggle("active");
						var panel = this.nextElementSibling;
						if (panel.style.maxHeight){
						panel.style.maxHeight = null;
						} else {
						panel.style.maxHeight = panel.scrollHeight + "px";
						} 
					});
					}
					</script>

				</div>
				<div class="col-md-6">
				</div>
				<div class="col-md-6">

				</div>
				
			</div>


			<?php
			/**
			 * @hooked pizzaro_homepage_content - 10
			 */
			//do_action( 'pizzaro_contactpage' ); ?>

		</main><!-- #main -->
	</div><!-- #primary -->

	<style>
		/*contact us styles */
		.contactTitle{
			text-transform: uppercase;
			margin-top: 10px;
    		color: #bba12a;
			text-align: left;
			/*text-decoration: underline;*/
			font-size: 24px;
			}

			.contactTitleHead{
				text-transform: uppercase;
			margin-top: 20px;
			margin-bottom: 20px;
    		color: #bba12a;
			text-align: center;
			text-decoration: underline;
			font-size: 24px;

			}

			.accordion {
						background-color: #eee;
						color: #444;
						cursor: pointer;
						padding: 18px;
						width: 100%;
						border: none;
						text-align: left;
						outline: none;
						font-size: 15px;
						transition: 0.4s;
						border-radius: 5px !important;
						}

						.active, .accordion:hover {
						background-color: #ccc;
						}

						.accordion:after {
						content: '\002B';
						color: #777;
						font-weight: bold;
						float: right;
						margin-left: 5px;
						}

						.active:after {
						content: "\2212";
						}

						.panel {
						padding: 0 18px;
						background-color: white;
						max-height: 0;
						overflow: hidden;
						transition: max-height 0.2s ease-out;
						box-shadow: none !important;
						}
		}
		</style>
		

	

	<?php 
	
get_footer();
