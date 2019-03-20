<?php
/**
 * Template Name: Search filter
 */

get_header();
?>

<script src="http://code.jquery.com/jquery-1.11.2.min.js" type="text/javascript"></script>

	<section id="primary" class="content-area">
		<main id="main" class="site-main">
		<div style="width:800px; margin:auto;">
		

		
<form action="<?php echo site_url() ?>/wp-admin/admin-ajax.php" method="POST" id="filter">
	<?php
		if( $terms = get_terms( array( 'taxonomy' => 'brand_name', 'orderby' => 'name' ) ) ) : 
 
			echo '<select name="categoryfilter"><option value="">Select brand_name...</option>';
			foreach ( $terms as $term ) :
				echo '<option value="' . $term->term_id . '">' . $term->name . '</option>'; // ID of the category as the value of an option
			endforeach;
			echo '</select>';
		endif;
	?>
		<?php
		if( $terms = get_terms( array( 'taxonomy' => 'maker_name', 'orderby' => 'name' ) ) ) : 
 
			echo '<select name="maker_name"><option value="">Select maker_name...</option>';
			foreach ( $terms as $term ) :
				echo '<option value="' . $term->term_id . '">' . $term->name . '</option>'; // ID of the category as the value of an option
			endforeach;
			echo '</select>';
		endif;
	?>
	<input type="text" name="price_min" placeholder="Min price" />
	<input type="text" name="price_max" placeholder="Max price" />	
	<button>Search</button>
	<input type="hidden" name="action" value="myfilter">
</form>
<div id="response"></div>
		
		
		
</div>
		</main><!-- #main -->
	</section><!-- #primary -->

<script>
jQuery(function($){
	jQuery('#filter').submit(function(){
		var filter = jQuery('#filter');
		jQuery.ajax({
			url:filter.attr('action'),
			data:filter.serialize(), // form data
			type:filter.attr('method'), // POST
			beforeSend:function(xhr){
				filter.find('button').text('Processing...'); // changing the button label
			},
			success:function(data){
				filter.find('button').text('Apply filter'); // changing the button label back
				jQuery('#response').html(data); // insert data
			}
		});
		return false;
	});
});

</script>

<?php
get_footer();