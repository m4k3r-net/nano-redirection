<?php
/**
 * @link              http://nanodesignsbd.com/
 * @since             1.0.0
 * @package           NanoRedirection
 *
 * @wordpress-plugin
 * Plugin Name:       NanoRedirection
 * Plugin URI:        http://nanodesignsbd.com/products/nano-redirection
 * Description:       Create a native, easy-to-use redirection being in WordPress database schema that works in single site and multisite instances
 * Version:           1.0.0
 * Author:            nanodesigns
 * Author URI:        http://nanodesignsbd.com/
 * Requires at least: 3.0
 * Tested up to:      4.4.2
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       'nano-redirection'
 * Domain Path:       /i18n/languages/
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// NECESSARY CONSTANTS
define( 'NR_VERSION', '1.0.0' );


/**
 * Translation-ready
 * 
 * Make the plugin translation-ready.
 * -----------------------------------------------------------------------
 */
add_action( 'init', 'nr_load_textdomain', 1 );

function nr_load_textdomain() {
    load_plugin_textdomain(
    	'nano-redirection',
    	FALSE,
    	dirname( plugin_basename( __FILE__ ) ) .'/i18n/languages/'
    );
}


/**
 * Styles & JavaScripts (Admin)
 * 
 * Necessary JavaScripts and Styles for Admin panel tweaks.
 * -----------------------------------------------------------------------
 */
add_action( 'admin_enqueue_scripts', 'nr_admin_scripts' );

function nr_admin_scripts() {
	$screen = get_current_screen();

	if( 'redirection' === $screen->post_type ) :

		wp_enqueue_style( 'nr-admin-style', plugins_url('/assets/css/nano-redirection-style.css', __FILE__) );
		wp_enqueue_script( 'nr-admin-scripts', plugins_url('/assets/js/nano-redirection-script.js', __FILE__), array('jquery'), NR_VERSION, true );

	endif;
}



/**
 * Custom Post Type: 'redirection'.
 * --------------------------------------------------------------------------
 */
add_action( 'init', 'nr_register_cpt_redirection' );

function nr_register_cpt_redirection() {

    $labels = array( 
        'name'                  => __( 'Redirection', 'nano-redirection' ),
        'singular_name'         => __( 'Redirection', 'nano-redirection' ),
        'add_new'               => __( 'Add New', 'nano-redirection' ),
        'add_new_item'          => __( 'Add New Redirection', 'nano-redirection' ),
        'edit_item'             => __( 'Edit Redirection', 'nano-redirection' ),
        'new_item'              => __( 'New Redirection', 'nano-redirection' ),
        'view_item'             => __( 'View Redirection', 'nano-redirection' ),
        'search_items'          => __( 'Search Redirection', 'nano-redirection' ),
        'not_found'             => __( 'No Redirection found', 'nano-redirection' ),
        'not_found_in_trash'    => __( 'No Redirection found in Trash', 'nano-redirection' ),
        'parent_item_colon'     => __( 'Parent Redirection:', 'nano-redirection' ),
        'menu_name'             => __( 'Redirection', 'nano-redirection' ),
    );

    $args = array( 
        'labels'                => $labels,
        'hierarchical'          => false,
        'description'           => '301 or 302 Redirections all over the site',
        'supports'              => array( '' ), //title
        'taxonomies'            => array( '' ),   
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => 'tools.php',
        'menu_position'         => 10,
        'menu_icon'             => 'dashicons-randomize',
        'show_in_nav_menus'     => false,
        'publicly_queryable'    => false,
        'exclude_from_search'   => true,
        'has_archive'           => false,
        'query_var'             => true,
        'can_export'            => true,
        'rewrite'               => array( 'slug' => 'redirection' ),
        'capability_type'       => 'post'
    );

    register_post_type( 'redirection', $args );
    
}


/** -------- META BOX -------- **/

add_action( 'add_meta_boxes', 'nr_redirection_meta_box' );

function nr_redirection_meta_box() {
    add_meta_box(
        'redirection-specifics',                            // metabox ID
        __( 'Redirection Specifications', 'nano-redirection' ),   // metabox title
        'redirection_callback',                             // callback function
        'redirection',                                      // post type (+ CPT)
        'normal',                                           // 'normal', 'advanced', or 'side'
        'high'                                              // 'high', 'core', 'default' or 'low'
    );
}


function redirection_callback() {
    global $post;

    // Use nonce for verification
    wp_nonce_field( basename( __FILE__ ), 'cpt_redirection_specifics_nonce' ); ?>

    <div class="row redirection-specifics-holder">

    	<!-- SAVING FROM URL TO THE 'post_title' FIELD OF 'posts' TABLE -->
        <p>
        	<label for="redirect-to"><?php _e( 'Redirect from', 'nano-redirection' ); ?></label><br>
        	<input type="url" name="post_title" id="redirect-from" value="<?php echo get_the_title( $post ); ?>" placeholder="<?php echo _x( 'Redirect from URL', 'placeholder', 'nano-redirection' ); ?>" required>
        </p>

        <!-- SAVING TO URL TO THE 'post_excerpt' FIELD OF 'posts' TABLE -->
        <p>
        	<label for="redirect-to"><?php _e( 'Redirect to', 'nano-redirection' ); ?></label><br>
        	<input type="url" name="excerpt" id="redirect-to" value="<?php echo get_post_field( 'post_excerpt', $post->ID ); ?>" placeholder="<?php echo _x( 'Redirect to URL', 'placeholder', 'nano-redirection' ); ?>" required>
        </p>

        <p>
        	<?php $status_meta = get_post_meta( $post->ID, 'http_status', true ); ?>
        	<label for="http-status"><?php _e( 'HTTP Status', 'nano-redirection' ); ?></label><br>
        	<select name="http_status" id="http-status" class="postform">
            	<option value="301" <?php selected( $status_meta, 301 ); ?>><?php _e('301 (Moved Permanently)', 'nano-redirection'); ?></option>
            	<option value="302" <?php selected( $status_meta, 302 ); ?>><?php _e('302 (Temporary Redirect)', 'nano-redirection'); ?></option>
          	</select>
        </p>

    </div> <!-- .row -->

    <?php
}


function save_redirection_specifics_meta( $post_id ) {   

    // verify nonce
    if (!isset($_POST['cpt_redirection_specifics_nonce']) || !wp_verify_nonce($_POST['cpt_redirection_specifics_nonce'], basename(__FILE__)))
        return $post_id;

    // check autosave
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
        return $post_id;
    
    // check permissions
    if ( 'redirection' == $_POST['post_type'] && !current_user_can('publish_post', $post_id) )
        return $post_id;
     
    $old_http_status 	= get_post_meta($post_id, 'http_status', true);
    $new_http_status 	= $_POST['http_status'];

    if ( $new_http_status && $new_http_status != $old_http_status )
    	update_post_meta( $post_id, 'http_status', $new_http_status );
    elseif ( '' == $new_http_status && $old_http_status )
    	delete_post_meta( $post_id, 'http_status', $old_http_status );

}

add_action( 'save_post',      'save_redirection_specifics_meta' );
add_action( 'new_to_publish', 'save_redirection_specifics_meta' );



/**
 * Add more columns to the Redirection CPT.
 * 
 * @param  array $columns Default columns.
 * @return array          Modified columns.
 * --------------------------------------------------------------------------
 */
function nr_redirection_columns( $columns ) {

  //Unset to reset existing columns
  unset( $columns['title'] );
  unset( $columns['date'] );
    
  $new_columns = array(
      'title'         => __( 'From URL', 'nano-redirection' ),
      'to_url'        => __( 'To URL', 'nano-redirection' ),
      'http_status'   => __( 'HTTP Status', 'nano-redirection' ),
      'date'          => __( 'Date', 'nano-redirection' ),
  );

  return array_merge( $columns, $new_columns );
}
add_filter( 'manage_redirection_posts_columns', 'nr_redirection_columns' );


/**
 * Populate the columns with the respective data.
 *
 * @since  1.0.0
 * 
 * @param  array $column    Default columns.
 * @param  integer $post_id That particular post_ID
 * @return void
 * --------------------------------------------------------------------------
 */
function nr_redirection_table_columns_data( $column, $post_id ) {
    global $project_prefix;
    switch ( $column ) {
        case 'to_url':
            echo '<span class="green">'. get_post_field( 'post_excerpt', $post_id ) .'</span>';
            break;

        case 'http_status':
            $http_status = get_post_meta( $post_id, "http_status", true );
            if( '301' === $http_status )
              printf( __('%s <span class="grey">(Moved Permanently)</span>', 'nano-redirection'), '<strong class="red">301</strong>' );
            else
              printf( __('%s <span class="grey">(Temporary Redirect)</span>', 'nano-redirection'), '<span class="orange">302</span>' );
            break;
    }
}
add_action( 'manage_redirection_posts_custom_column', 'nr_redirection_table_columns_data', 10, 2 );


/**
 * Do the 404 Redirection
 *
 * If the it's a not found URL, redirect the post to its
 * designated URL.
 *
 * @since  1.0.0
 * 
 * @return void
 * --------------------------------------------------------------------------
 */
function nr_force_redirect() {
    if( ! is_404() )
        return;
    
    //Grab the URL
    $this_url = nr_get_current_url();

    if( ! $this_url )
      return;

    //Fetch if any redirection is made manually for the 404 URL
    $redir_post = get_page_by_title( $this_url, OBJECT, 'redirection' );

    if( NULL === $redir_post )
      return;

    $post_id = $redir_post->ID;

    global $project_prefix;
    $to_url   = get_post_field( 'post_excerpt', $post_id );
    $status   = get_post_meta( $post_id, "http_status", true );
    $status   = $status ? $status : 301;

    wp_redirect( esc_url($to_url), $status );
    exit();
}
add_action( 'template_redirect', 'nr_force_redirect' );


/**
 * Get Full Current URL
 * 
 * Utility function to get the full address of the current request.
 * 
 * @link 	http://www.phpro.org/examples/Get-Full-URL.html
 * 
 * @since  2.0.2
 * 
 * @return string Current URL.
 * --------------------------------------------------------------------------
 */
function nr_get_current_url() {
	/* Check for https */
	$protocol = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';

	/* Return the full address */
	return $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}



/** ------------------------------------ **
            NanoRedirection API
**  ------------------------------------ **/

/**
 * Create Redirection
 *
 * A helper function for the part of the API will let you use this plugin
 * for any plugin or theme as an extension. The function will create a
 * redirection for 2 specific URL and will redirect using HTTP status code.
 *
 * @since  1.0.0
 * 
 * @param  string $from_url The URL from which to redirect to.
 * @param  string $to_url   The URL to which to redirect to.
 * @param  string $status   HTTP status code, 301 and 302 accepted.
 * @return void
 * --------------------------------------------------------------------------
 */
function nr_create_redirection( $from_url = null, $to_url = null, $status = '301' ) {

    if( null === $from_url && null === $to_url )
        return;

    $user_id = get_current_user_id();

    // Insert the post into the database
    $post_id = wp_insert_post( array(
      'post_title'    => $from_url,
      'post_status'   => 'publish',
      'post_excerpt'  => $to_url,
      'post_author'   => $user_id,
      'post_type'     => 'redirection',
    ) );

    $status = '302' === $status ? $status : '301';

    update_post_meta( $post_id, 'http_status', $status );

}