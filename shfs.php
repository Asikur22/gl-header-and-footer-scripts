<?php
/**
 * Plugin Name: GL Header and Footer Scripts
 * Plugin URI: https://greenlifeit.com
 * Description: Allows you to insert code or text in the header or footer of your WordPress site
 * Version: 1.0.0
 * Author: Asiqur Rahman
 * Author URI: https://asique.net
 * Text Domain: glhfs
 * Domain Path: /lang
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

define( 'SHFS_PLUGIN_DIR', str_replace( '\\', '/', dirname( __FILE__ ) ) );
define( 'SHFS_PLUGIN_SLUG', basename( dirname( __FILE__ ) ) );

if ( ! class_exists( 'GLHeaderAndFooterScripts' ) ) {
	
	class GLHeaderAndFooterScripts {
		
		function __construct() {
			add_action( 'init', array( &$this, 'init' ) );
			add_action( 'admin_init', array( &$this, 'admin_init' ) );
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
			add_action( 'wp_head', array( &$this, 'wp_head' ) );
			add_action( 'wp_footer', array( &$this, 'wp_footer' ) );
			
			add_action( 'admin_print_styles-post-new.php', array( &$this, 'codemirror_enqueue_scripts' ) );
			add_action( 'admin_print_styles-post.php', array( &$this, 'codemirror_enqueue_scripts' ) );
			
			if ( isset( $_GET['page'] ) && $_GET['page'] == SHFS_PLUGIN_SLUG ) {
				add_action( 'admin_enqueue_scripts', array( &$this, 'codemirror_enqueue_scripts' ) );
			}
			
		}
		
		function codemirror_enqueue_scripts() {
			$cm_settings['codeEditor'] = wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
			wp_localize_script( 'jquery', 'cm_settings', $cm_settings );
			
			wp_enqueue_style( 'wp-codemirror' );
			wp_enqueue_style( 'glhfs-codemirror-css', plugins_url( '/css/glhfs-codemirror.min.css', __FILE__ ) );
			
			wp_enqueue_script( 'wp-theme-plugin-editor' );
			wp_enqueue_script( 'glhfs-codemirror', plugins_url( '/js/glhfs-codemirror.min.js', __FILE__ ), array( 'wp-theme-plugin-editor' ) );
		}
		
		function init() {
			load_plugin_textdomain( 'glhfs', false, SHFS_PLUGIN_SLUG . '/lang' );
		}
		
		function admin_init() {
			// register settings for sitewide script
			register_setting( 'header-and-footer-scripts', 'shfs_insert_header', 'trim' );
			register_setting( 'header-and-footer-scripts', 'shfs_insert_footer', 'trim' );
			register_setting( 'header-and-footer-scripts', 'shfs_post_types' );
			
			// add meta box to all post types
			$post_type = get_option( 'shfs_post_types', array( 'page' => '1' ) );
			if ( is_array( $post_type ) ) {
				foreach ( array_keys( $post_type ) as $type ) {
					add_meta_box( 'shfs_all_post_meta', esc_html__( 'Insert Script to &lt;head&gt;', 'glhfs' ), 'shfs_meta_setup', $type, 'normal', 'high' );
				}
			}
			
			add_action( 'save_post', 'shfs_post_meta_save' );
		}
		
		// adds menu item to wordpress admin dashboard
		function admin_menu() {
			add_submenu_page( 'options-general.php', __( 'Header and Footer Scripts', 'glhfs' ), __( 'Header and Footer Scripts', 'glhfs' ),
				'manage_options', SHFS_PLUGIN_SLUG, array( &$this, 'shfs_options_panel' ) );
		}
		
		function wp_head() {
			$meta = get_option( 'shfs_insert_header', '' );
			if ( $meta != '' ) {
				echo do_shortcode( $meta ), "\n";
			}
			
			$shfs_post_meta = get_post_meta( get_the_ID(), '_inpost_head_script', true );
			if ( $shfs_post_meta != '' ) {
				echo do_shortcode( $shfs_post_meta['synth_header_script'] ), "\n";
			}
			
		}
		
		function wp_footer() {
			if ( ! is_admin() && ! is_feed() && ! is_robots() && ! is_trackback() ) {
				$text = get_option( 'shfs_insert_footer', '' );
				$text = convert_smilies( $text );
				$text = do_shortcode( $text );
				
				if ( $text != '' ) {
					echo $text, "\n";
				}
			}
		}
		
		function shfs_options_panel() {
			// Load options page
			require_once( SHFS_PLUGIN_DIR . '/inc/options.php' );
		}
	}
	
	function shfs_meta_setup() {
		global $post;
		
		// using an underscore, prevents the meta variable
		// from showing up in the custom fields section
		$meta = get_post_meta( $post->ID, '_inpost_head_script', true );
		
		// instead of writing HTML here, lets do an include
		include_once( SHFS_PLUGIN_DIR . '/inc/meta.php' );
		
		// create a custom nonce for submit verification later
		echo '<input type="hidden" name="shfs_post_meta_noncename" value="' . wp_create_nonce( __FILE__ ) . '" />';
	}
	
	function shfs_post_meta_save( $post_id ) {
		// authentication checks
		
		// make sure data came from our meta box
		if ( ! isset( $_POST['shfs_post_meta_noncename'] )
		     || ! wp_verify_nonce( $_POST['shfs_post_meta_noncename'], __FILE__ ) ) {
			return $post_id;
		}
		
		// check user permissions
		if ( $_POST['post_type'] == 'page' ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}
		
		$current_data = get_post_meta( $post_id, '_inpost_head_script', true );
		$new_data = $_POST['_inpost_head_script'];
		shfs_post_meta_clean( $new_data );
		
		if ( $current_data ) {
			if ( is_null( $new_data ) ) {
				delete_post_meta( $post_id, '_inpost_head_script' );
			} else {
				update_post_meta( $post_id, '_inpost_head_script', $new_data );
			}
		} elseif ( ! is_null( $new_data ) ) {
			add_post_meta( $post_id, '_inpost_head_script', $new_data, true );
		}
		
		return $post_id;
	}
	
	function shfs_post_meta_clean( &$arr ) {
		if ( is_array( $arr ) ) {
			foreach ( $arr as $i => $v ) {
				if ( is_array( $arr[ $i ] ) ) {
					shfs_post_meta_clean( $arr[ $i ] );
					
					if ( ! count( $arr[ $i ] ) ) {
						unset( $arr[ $i ] );
					}
				} else {
					if ( trim( $arr[ $i ] ) == '' ) {
						unset( $arr[ $i ] );
					}
				}
			}
			
			if ( ! count( $arr ) ) {
				$arr = null;
			}
		}
	}
	
	$glhfs = new GLHeaderAndFooterScripts();
}
