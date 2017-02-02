<?php

// Add options page (ACF Controller)
if( function_exists('acf_add_options_page') ) {
	acf_add_options_page();
}

if ( ! class_exists( 'Timber' ) ) {
	add_action( 'admin_notices', function() {
			echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php' ) ) . '</a></p></div>';
		} );
	return;
}

// Stop random p's
function remove_the_wpautop_function() {
    remove_filter( 'the_content', 'wpautop' );
    remove_filter( 'the_excerpt', 'wpautop' );
}

add_action( 'after_setup_theme', 'remove_the_wpautop_function' );

// Allow SVGs
function cc_mime_types($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');

// Change author to staff
global $wp_rewrite;
$wp_rewrite->author_base = "staff";
$wp_rewrite->flush_rules();

Timber::$dirname = array('templates', 'views');

class StarterSite extends TimberSite {

	function __construct() {
		add_theme_support( 'post-formats' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'menus' );
		add_filter( 'timber_context', array( $this, 'add_to_context' ) );
		add_filter( 'get_twig', array( $this, 'add_to_twig' ) );
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		parent::__construct();
	}

  function register_post_types() {
    register_post_type( 'project',
      // CPT Options
      array(
        'labels' => array(
        'name' => __( 'Projects' ),
        'singular_name' => __( 'Project' )
      ),
        'menu_icon' => 'dashicons-megaphone',
        'supports' => array( 'title', 'editor', 'thumbnail' ),
        'public' => true,
        'has_archive' => false,
        'rewrite' => array('slug' => 'project'),
      )
    );
  }

	function register_taxonomies() {
		//this is where you can register custom taxonomies
	}

	function add_to_context( $data ) {
    $data['menu'] = new TimberMenu('main');
    $data['topnav'] = new TimberMenu('2');
    $data['footernav'] = new TimberMenu('3');
    $data['options'] = get_fields('option');
    $data['projects'] = Timber::get_posts('post_type=project');
		return $data;
	}

  // Extras
	function add_to_twig( $twig ) {
		/* this is where you can add your own fuctions to twig */
		$twig->addExtension( new Twig_Extension_StringLoader() );
		$twig->addFilter('myfoo', new Twig_SimpleFilter('myfoo', array($this, 'myfoo')));
		return $twig;
	}
}

// Call the google CDN version of jQuery for the frontend
// Make sure you use this with wp_enqueue_script('jquery'); in your header
function wpfme_jquery_enqueue() {
	wp_deregister_script('jquery');
	wp_register_script('jquery', "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . "://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js", false, null);
	wp_enqueue_script('jquery');
}
if (!is_admin()) add_action("wp_enqueue_scripts", "wpfme_jquery_enqueue", 11);

new StarterSite();
