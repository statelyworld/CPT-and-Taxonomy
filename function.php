<?php
function brand_name_init() {
	// create a new taxonomy
	   register_taxonomy(
        'brand_name',
        'car',
        array(
            'labels' => array(
                'name' => 'Brand name',
                'add_new_item' => 'Add New Brand',
                'new_item_name' => "New Brand Type"
            ),
            'show_ui' => true,
            'show_tagcloud' => false,
            'hierarchical' => true
        )
    );
}
add_action( 'init', 'brand_name_init' );

add_action( 'init', 'maker_name_init', 0 );
function maker_name_init() {
    register_taxonomy(
        'maker_name',
        'car',
        array(
            'labels' => array(
                'name' => 'Maker name',
                'add_new_item' => 'Add New Maker',
                'new_item_name' => "New Maker Type"
            ),
            'show_ui' => true,
            'show_tagcloud' => false,
            'hierarchical' => true
        )
    );
}


function register_my_cpts() {

	/**
	 * Post Type: Cars.
	 */

	$labels = array(
		"name" => __( "Cars", "twentynineteen" ),
		"singular_name" => __( "Car", "twentynineteen" ),
	);

	$args = array(
		"label" => __( "Cars", "twentynineteen" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"delete_with_user" => false,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "car", "with_front" => true ),
		"query_var" => true,
		"supports" => array( "title", "editor", "thumbnail" ),
		"taxonomies" => array( "brand_name", "maker_name" ),
	);

	register_post_type( "car", $args );
}

add_action( 'init', 'register_my_cpts' );


class Car_Price_Meta_Box {

	public function __construct() {

		if ( is_admin() ) {
			add_action( 'load-post.php',     array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
		}

	}

	public function init_metabox() {

		add_action( 'add_meta_boxes', array( $this, 'add_metabox'  )        );
		add_action( 'save_post',      array( $this, 'save_metabox' ), 10, 2 );

	}

	public function add_metabox() {

		add_meta_box(
			'car_price',
			__( 'Car Price', 'text_domain' ),
			array( $this, 'render_metabox' ),
			'car',
			'advanced',
			'default'
		);

	}

	public function render_metabox( $post ) {

		// Add nonce for security and authentication.
		wp_nonce_field( 'car_nonce_action', 'car_nonce' );

		// Retrieve an existing value from the database.
		$car_price = get_post_meta( $post->ID, 'car_price', true );		

		// Set default values.
		if( empty( $car_price ) ) $car_price = '';	

		// Form fields.
		echo '<table class="form-table">';

		echo '	<tr>';
		echo '		<th><label for="car_price" class="car_price_label">' . __( 'Price', 'text_domain' ) . '</label></th>';
		echo '		<td>';
		echo '			<input type="number" id="car_price" name="car_price" class="car_price_field" placeholder="' . esc_attr__( '', 'text_domain' ) . '" value="' . esc_attr__( $car_price ) . '">';
		echo '			<p class="description">' . __( 'Price', 'text_domain' ) . '</p>';
		echo '		</td>';
		echo '	</tr>';
		echo '</table>';

	}

	public function save_metabox( $post_id, $post ) {

		// Add nonce for security and authentication.
		$nonce_name   = $_POST['car_nonce'];
		$nonce_action = 'car_nonce_action';

		// Check if a nonce is set.
		if ( ! isset( $nonce_name ) )
			return;

		// Check if a nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) )
			return;

		// Check if the user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;

		// Check if it's not an autosave.
		if ( wp_is_post_autosave( $post_id ) )
			return;

		// Check if it's not a revision.
		if ( wp_is_post_revision( $post_id ) )
			return;

		// Sanitize user input.
		$car_new_price = isset( $_POST[ 'car_price' ] ) ? sanitize_text_field( $_POST[ 'car_price' ] ) : '';
		$car_new_currency = isset( $_POST[ 'car_currency' ] ) ? $_POST[ 'car_currency' ] : '';

		// Update the meta field in the database.
		update_post_meta( $post_id, 'car_price', $car_new_price );
		update_post_meta( $post_id, 'car_currency', $car_new_currency );

	}

}

new Car_Price_Meta_Box;





add_action('wp_ajax_myfilter', 'gj_filter_function'); // wp_ajax_{ACTION HERE} 
add_action('wp_ajax_nopriv_myfilter', 'gj_filter_function');
 
function gj_filter_function(){
	$args = array(
		'orderby' => 'date', // we will sort posts by date
		'order'	=> $_POST['date'] // ASC or DESC
	);
 
	// for taxonomies / categories
	if( isset( $_POST['categoryfilter'] ) )
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'brand_name',
				'field' => 'id',
				'terms' => $_POST['categoryfilter']
			)
		);
 
	// create $args['meta_query'] array if one of the following fields is filled
	if( isset( $_POST['price_min'] ) && $_POST['price_min'] || isset( $_POST['price_max'] ) && $_POST['price_max']  )
		$args['meta_query'] = array( 'relation'=>'AND' ); // AND means that all conditions of meta_query should be true
 
	// if both minimum price and maximum price are specified we will use BETWEEN comparison
	if( isset( $_POST['price_min'] ) && $_POST['price_min'] && isset( $_POST['price_max'] ) && $_POST['price_max'] ) {
		$args['meta_query'][] = array(
			'key' => '_price',
			'value' => array( $_POST['price_min'], $_POST['price_max'] ),
			'type' => 'numeric',
			'compare' => 'between'
		);
	} else {
		// if only min price is set
		if( isset( $_POST['price_min'] ) && $_POST['price_min'] )
			$args['meta_query'][] = array(
				'key' => '_price',
				'value' => $_POST['price_min'],
				'type' => 'numeric',
				'compare' => '>'
			);
 
		// if only max price is set
		if( isset( $_POST['price_max'] ) && $_POST['price_max'] )
			$args['meta_query'][] = array(
				'key' => '_price',
				'value' => $_POST['price_max'],
				'type' => 'numeric',
				'compare' => '<'
			);
	}
	
	$args = array(
 'post_type'        => 'car',
'posts_per_page'   => 5
);
 
	$query = new WP_Query( $args );
 
	if( $query->have_posts() ) :
		while( $query->have_posts() ): $query->the_post();
			echo '<h2>' . $query->post->post_title . '</h2>';
		endwhile;
		wp_reset_postdata();
	else :
		echo 'No posts found';
	endif;
 
	die();
}
?>