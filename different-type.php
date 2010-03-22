<?php
/**
 * @package Different Type
 * @author Frank B&uuml;ltge
 * @version 0.1
 */
 
/*
	Plugin Name: Different Type
	Plugin URI: http://bueltge.de/
	Description: Add different types to posts
	Author: Frank B&uuml;ltge
	Version: 0.1
	License: GPL
	Author URI: http://bueltge.de/
	Last change: 24.12.2009 00:00:00
*/

/**
 * Example for use outside the loop:
 * <?php the_DifferentTypeFacts($post->ID); ?>
 * @param $id Integer - Post-ID
 * @param $type String - heading, additional-info, listdata (default is ''-empty)
 *
 * Example: <?php the_DifferentTypeFacts($post->ID, 'heading'); ?>
 */

//avoid direct calls to this file, because now WP core and framework has been used
if ( !function_exists('add_action') ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

if ( function_exists('add_action') ) {
	//WordPress definitions
	if ( !defined('WP_CONTENT_URL') )
		define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
	if ( !defined('WP_CONTENT_DIR') )
		define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
	if ( !defined('WP_PLUGIN_URL') )
		define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');
	if ( !defined('WP_PLUGIN_DIR') )
		define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');
	if ( !defined('PLUGINDIR') )
		define( 'PLUGINDIR', 'wp-content/plugins' ); // Relative to ABSPATH.  For back compat.
	if ( !defined('WP_LANG_DIR') )
		define('WP_LANG_DIR', WP_CONTENT_DIR . '/languages');
	
	// plugin definitions
	define( 'FB_DT_BASENAME', plugin_basename(__FILE__) );
	define( 'FB_DT_BASEDIR', dirname( plugin_basename(__FILE__) ) );
	define( 'FB_DT_TEXTDOMAIN', 'different-types' );
}

if ( !class_exists( 'DifferentType' ) ) {
	class DifferentType {
		
		// constructor
		function DifferentType() {
			
			if (is_admin() ) {
				add_action( 'admin_init', array(&$this, 'on_admin_init') );
				add_action( 'wp_insert_post', array(&$this, 'on_wp_insert_post'), 10, 2 );
				add_action( 'init', array(&$this, 'textdomain') );
				register_uninstall_hook( __FILE__, array(&$this, 'uninstall') );
				add_action( "admin_print_scripts-post.php", array($this, 'enqueue_script') );
				add_action( "admin_print_scripts-post-new.php", array($this, 'enqueue_script') );
				add_action( "admin_print_scripts-page.php", array($this, 'enqueue_script') );
				add_action( "admin_print_scripts-page-new.php", array($this, 'enqueue_script') );
			}
		}
		
		// active for multilanguage
		function textdomain() {
			
			if ( function_exists('load_plugin_textdomain') )
				load_plugin_textdomain( FB_DT_TEXTDOMAIN, false, dirname( FB_DT_BASENAME ) . '/languages' );
		}
		
		// unsintall all postmetadata
		function uninstall() {
			
			$all_posts = get_posts('numberposts=0&post_type=post&post_status=');
			
			foreach( $all_posts as $postinfo) {
				delete_post_meta($postinfo->ID, '_different-types');
			}
		}
		
		// add script
		function enqueue_script() {
			wp_enqueue_script( 'tinymce4dt', WP_PLUGIN_URL . '/' . FB_DT_BASEDIR . '/js/script.js', array('jquery') );
		}
		
		// admin init
		function on_admin_init() {
			
			if ( !current_user_can( 'publish_posts' ) )
				return;
			
			add_meta_box( 'hotel_helper',
									__( 'Different Types', FB_DT_TEXTDOMAIN ),
									array( &$this, 'meta_box' ),
									'post', 'normal', 'high'
									);
									
			// remove meta box for trackbacks
			remove_meta_box('trackbacksdiv', 'post', 'normal');
			// remove meta box for custom fields
			remove_meta_box('postcustom', 'post', 'normal');
		}
		
		// check for preview
		function is_page_preview() {
			$id = (int)$_GET['preview_id'];
			if ($id == 0) $id = (int)$_GET['post_id'];
			$preview = $_GET['preview'];
			if ($id > 0 && $preview == 'true') {
				global $wpdb;
				$type = $wpdb->get_results("SELECT post_type FROM $wpdb->posts WHERE ID=$id");
				if ( count($type) && ($type[0]->post_type == 'page') && current_user_can('edit_page') )
					return true;
			}
			return false;
		}
		
		// after save post, save meta data for plugin
		function on_wp_insert_post($id) {
			global $id;
			
			if ( !isset($id) )
				$id = (int)$_REQUEST['post_ID'];
			if ( $this->is_page_preview() && !isset($id) )
				$id = (int)$_GET['preview_id'];
			
			if ( !current_user_can('edit_post') )
				return;
			
			if ( isset($_POST['dt-heading']) && $_POST['dt-heading'] != '' )
				$this->data['heading'] = esc_attr( $_POST['dt-heading'] );
			if ( isset($_POST['dt-additional-info']) && $_POST['dt-additional-info'] != '' )
				$this->data['additional-info'] = $_POST['dt-additional-info'];
			if ( isset($_POST['dt-listdata']) && $_POST['dt-listdata'] != '' )
				$this->data['listdata'] = esc_attr( $_POST['dt-listdata'] );
			
			if ( isset($this->data) && $this->data != '' )
				update_post_meta($id, '_different-types', $this->data);
		}

		// load post_meta_data
		function load_post_meta($id) {
			
			return get_post_meta($id, '_different-types', true);
		}

		// meta box on post/page
		function meta_box($data) {
			
			$value = $this->load_post_meta($data->ID);
			?>
			<table id="dt-page-definition" width="100%" cellspacing="5px">
				<tr valign="top">
					<td style="width:20%;"><label for="dt-heading"><?php _e( 'Subtitle:', FB_DT_TEXTDOMAIN ); ?></label></td>
					<td><input type="text" id="dt-heading" name="dt-heading" class="heading form-input-tip" size="16" autocomplete="off" value="<?php echo $value['heading']; ?>" tabindex="6" style="width:99.5%"/></td>
				</tr>
				<tr valign="top">
					<td><label for="dt-additional-info"><?php _e( 'Additional information:', FB_DT_TEXTDOMAIN ); ?></label></td>
					<td><textarea cols="16" rows="5" id="dt-additional-info" name="dt-additional-info" class="additional-info form-input-tip code" size="20" autocomplete="off" tabindex="6" style="width:90%"/><?php echo wpautop( $value['additional-info'] ); ?></textarea>
						<table id="post-status-info" cellspacing="0" style="line-height: 24px;">
							<tbody>
								<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
				<tr valign="top">
					<td><label for="dt-listdata"><?php _e( 'Listdata:', FB_DT_TEXTDOMAIN ); ?></label></td>
					<td><textarea cols="16" rows="10" id="dt-listdata" name="dt-listdata" class="listdata form-input-tip" size="20" autocomplete="off" tabindex="6" style="width:99.5%"/><?php echo $value['listdata']; ?></textarea><br /><small><?php _e( 'One list per line', FB_DT_TEXTDOMAIN ) ?></small></td>
				</tr>
			</table>
			<?php
		}

		// return facts incl. markup
		function get_DifferentTypeFacts($id, $type, $value) {
			
			if (!$value)
				return false;
			if ( $type == '' )
				return false;
			
			if ( 'heading' == $type && '' != $value['heading'] )
				return $value['heading'];
			if ( 'additional-info' == $type && '' != $value['additional-info'] )
				return wpautop( wptexturize($value['additional-info']) );
			if ( 'listdata' == $type && '' != $value['listdata'] ) {
				$return = '';
				$listdatas = preg_split("/\r\n/", $value['listdata'] );
				foreach ( (array) $listdatas as $key => $listdata ) {
					$return .= '<li>' . trim($listdata) . '</li>';
				}
				return '<ul>' . $return . '</ul>'. "\n";
			}
		}
		
		// echo facts, if exists
		function DifferentTypeFacts($id, $type, $string) {
		
			if ( $id ) {
				$value = $this->load_post_meta($id);
				
				echo $this->get_DifferentTypeFacts($id, $type, $value);
			}
		}

	} // End class
	
	// instance class
	$DifferentType = new DifferentType();
	
	
	// use in template
	function the_DifferentTypeFacts($id, $type = '', $string = '') {
		global $DifferentType;
		
		$DifferentType->DifferentTypeFacts($id, $type, $string);
	}
	
} // End if class exists statement
?>
