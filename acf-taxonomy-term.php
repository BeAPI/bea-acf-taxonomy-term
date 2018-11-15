<?php
/**
* Plugin Name: Advanced Custom Fields: Taxonomy term
* Description: Add taxonomies terms selector
* Version: 1.0.4
* Author: Be API Technical Team
* Author URI: www.beapi.fr
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class bea_acf_field_taxonomy_term_plugin {
	/**
	*  Construct
	*
	*  @description:
	*  @since: 3.6
	*  @created: 1/04/13
	 * @author Nicolas Juen
	*/

	function __construct() {
		// version 4+
		add_action( 'acf/register_fields', array( __CLASS__, 'register_fields' ) );

		// version 5+
		add_action( 'acf/include_field_types', array( __CLASS__, 'register_field_v5' ) );
	}

	/**
	*  register_fields
	*
	*  @description:
	*  @since: 3.6
	*  @created: 1/04/13
	 * @author Nicolas Juen
	*/
	public static function register_fields() {
		include_once( 'taxonomy-term-v4.php' );
	}

	/**
	 * Register_fields
	 *
	 * @description:
	 * @since: 3.6
	 * @created: 13/04/2016
	 * @author Julien Maury
	 */
	public static function register_field_v5() {
		include_once( 'taxonomy-term-v5.php' );
	}


}

add_action( 'init', 'bea_acf_field_taxonomy_init' );
function bea_acf_field_taxonomy_init(){
	load_plugin_textdomain( 'bea-acf-tt', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action( 'plugins_loaded', 'bea_acf_field_taxonomy_term_load' );
function bea_acf_field_taxonomy_term_load() {
	new bea_acf_field_taxonomy_term_plugin();
}
