<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

/**
 * Class acf_field_taxonomy_term
 */
class bea_acf_field_taxonomy_term extends acf_field {
	// vars
	public $settings; // will hold info such as dir / path
	public $defaults; // will hold default field options


	/*
	*  __construct
	*
	*  Set name / label needed for actions / filters
	*
	*  @since	3.6
	*  @date	23/01/13
	*/

	function __construct() {
		// vars
		$this->name     = 'taxonomy_term';
		$this->label    = __( 'Term Taxonomy Selector', 'bea-acf-tt' );
		$this->category = __( "Basic", 'acf' ); // Basic, Content, Choice, etc
		$this->defaults = array( 'post_type' => 'all_taxonomies', 'allow_multiple' => 1 );


		// do not delete!
		parent::__construct();


		// settings
		$this->settings = array( 'path' => apply_filters( 'acf/helpers/get_path', __FILE__ ),
			'dir' => apply_filters( 'acf/helpers/get_dir', __FILE__ ),
			'version' => '1.0.0' );

		add_action( 'wp_ajax_' . 'bea_acf_taxonomy_term', array( __CLASS__, 'a_get_terms' ) );

		add_action( 'admin_footer', array( __CLASS__, 'add_js_template' ) );

	}


	/**
	 * Enqueue admin scripts

	 */
	function input_admin_enqueue_scripts() {
		// Scripts
		wp_register_script( 'select2', $this->settings['dir'] . 'assets/js/lib/select2/select2.min.js', array( 'jquery' ), true );
		wp_register_script( 'acf-input-taxonomy-term', $this->settings['dir'] . 'assets/js/input.js', array( 'jquery',
			'underscore',
			'select2' ), $this->settings['version'] );

		wp_localize_script( 'acf-input-taxonomy-term', 'bea_acf_taxonomy_term', array( 'nonce' => wp_create_nonce( 'bea_acf_taxonomy_term' ) ) );

		// Style
		wp_register_style( 'select2', $this->settings['dir'] . 'assets/js/lib/select2/select2.css' );

		// Enqueuing
		wp_enqueue_script( 'acf-input-taxonomy-term' );
		wp_enqueue_script( 'select2' );
		wp_enqueue_style( 'select2' );
	}

	/**
	 * Javascript admin footer template for the selector


	 */
	public static function add_js_template() {
		?>
		<script type="text/html" id="tmpl-bea-taxonomy-term">
			<% _.each( terms, function ( term ) { %>
				<option <%= be_acf_taxonomy_term_selected( _.contains( selected_terms, term.term_id ), true ) %>
					value="<%- term.term_id %>"><%- term.name %></option>
			<% } ); %>
		</script>
	<?php
	}



	/*
	*  create_options()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/

	/**
	 * @param $field
	 */
	function create_options( $field ) {
		// defaults?
		$field = wp_parse_args( $field, $this->defaults );
		// key is needed in the field names to correctly save the data
		$key = $field['name'];

		$post_types                             = (array)get_post_types( array( 'public' => true ), 'object' );
		$post_types_available                   = array();
		$post_types_available['all_taxonomies'] = __( 'All custom post types', 'bea-acf-tt' );
		foreach( $post_types as $post_type ) {
			$post_types_available[$post_type->name] = $post_type->label;
		}

		// Create Field Options HTML
		?>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php esc_html_e( 'Post types' ); ?></label>

				<p class="description"><?php esc_html_e( 'Post types to use for taxonomies', 'bea-acf-tt' ); ?></p>
			</td>
			<td>
				<?php
				do_action( 'acf/create_field', array( 'type' => 'select',
					'name' => 'fields[' . $key . '][post_type]',
					'value' => $field['post_type'],
					'layout' => 'horizontal',
					'choices' => $post_types_available ) );

				?>
			</td>
		</tr>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label>Multiple</label>

				<p class="description"><?php esc_html_e( 'Allows selection of multiple terms', 'bea-acf-tt' ); ?></p>
			</td>
			<td>
				<?php
				do_action( 'acf/create_field', array( 'type' => 'radio',
					'name' => 'fields[' . $key . '][allow_multiple]',
					'value' => $field['allow_multiple'],
					'choices' => array( 0 => "Non",
						1 => "Oui", ) ) );
				?>
			</td>
		</tr>
	<?php

	}

	/**
	 * Ajax action for getting the terms

	 */
	public static function a_get_terms() {
		if( !check_ajax_referer( 'bea_acf_taxonomy_term', false, false ) ) {
			wp_send_json_error();
		}
		$taxonomies = isset( $_POST['taxonomies'] ) ? $_POST['taxonomies'] : array();
		$terms      = get_terms( $taxonomies, array( 'hide_empty' => false ) );

		if( is_wp_error( $terms ) || empty( $terms ) ) {
			wp_send_json_error();
		}

		wp_send_json_success( $terms );
	}


	/*
	*  create_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/

	/**
	 * @param $field
	 */
	function create_field( $field ) {
		$screen = get_current_screen();

		if( isset( $field['post_type'] ) && !empty( $field['post_type'] ) ) {
			if( 'all_taxonomies' == $field['post_type'] ) {
				$taxonomies = get_taxonomies( array( 'show_ui' => true ), 'objects' );
			} else {
				$taxonomies = get_object_taxonomies( $field['post_type'], 'objects' );
			}
		} else {
			$taxonomies = get_object_taxonomies( $screen->post_type, 'objects' );
		}

		$values              = wp_parse_args( $field['value'], array( 'taxonomies' => array(), 'terms' => array() ) );
		$taxonomies_selected = (array)$values['taxonomies'];
		$terms_selected      = (array)$values['terms'];
		$terms               = get_terms( $taxonomies_selected, array( 'hide_empty' => false ) );
		?>
		<span class="acf-label"><?php esc_html_e( 'Taxonomies', 'bea-acf-tt' ); ?></span>
        <label for="bea_acf_tt_tax"><?php esc_html_e( 'Choose 1 or more taxonomies', 'bea-acf-tt' ); ?></label>
        <select id="bea_acf_tt_tax"
				class="bea_acf_taxonomy_term_taxonomies widefat" <?php __checked_selected_helper( true, $field['allow_multiple'], true, 'multiple' ); ?>
				name="<?php echo esc_attr( $field['name'] ); ?>[taxonomies][]">
			<option value=""> <?php esc_html_e( 'None', 'bea-acf-tt' )?></option>
			<?php foreach( $taxonomies as $taxonomy ):
				if( empty( $taxonomy->object_type ) ) {
					continue;
				}
				?>
				<option <?php selected( in_array( $taxonomy->name, $taxonomies_selected ), true ); ?>
					value="<?php echo esc_attr( $taxonomy->name ); ?>"><?php echo esc_html( $taxonomy->labels->name.self::extract_post_types( $taxonomy ) ); ?></option>
			<?php endforeach; ?>
		</select>

		<span class="acf-label"><?php esc_html_e( 'Terms', 'bea-acf-tt' ); ?></span>
        <label for="bea_acf_tt_allow_multiple"><?php esc_html_e( 'Choose 1 or more terms that belong to these taxonomies', 'bea-acf-tt' ); ?></label>
        <select id="bea_acf_tt_allow_multiple multiple multiple=True"
				class="bea_acf_taxonomy_term_taxonomies_terms widefat" <?php __checked_selected_helper( true, $field['allow_multiple'], true, 'multiple' ); ?>
				name="<?php echo esc_attr( $field['name'] ); ?>[terms][]">
			<option value=""> <?php esc_html_e( 'None', 'bea-acf-tt' )?></option>
			<?php foreach( $terms as $term ): ?>
				<option <?php selected( in_array( $term->term_id, $terms_selected ), true ); ?>
					value="<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></option>
			<?php endforeach; ?>
		</select>
	<?php
	}

	/**
	 * @param stdClass $taxonomy : a Taxonomy base object
	 * @param string   $before
	 * @param string   $after
	 *
	 * @return bool|string
	 * @author Nicolas Juen
	 */
	private static function extract_post_types( stdClass $taxonomy, $before = ' (', $after = ')' ) {
		if( empty( $taxonomy->object_type ) ) {
			return false;
		}

		$post_types = wp_list_pluck( array_map( 'get_post_type_object', $taxonomy->object_type ), 'label' );
		return $before.implode( ', ', $post_types ).$after;
	}
}

// create field
new bea_acf_field_taxonomy_term();
