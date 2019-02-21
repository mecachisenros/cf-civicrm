<?php

/**
 * CiviCRM Caldera Forms Forms Class
 *
 * @since 0.4
 */
class CiviCRM_Caldera_Forms_Forms {

	/**
	 * Plugin reference.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var object $plugin The plugin instance
	 */
	public $plugin;
	
	/**
	 * Transient Id reference.
	 *
	 * @since 0.4.4
	 * @access protected
	 * @var string $transient_id The transient id reference
	 */
	protected $transient_id;

	/**
	 * Initialises this object.
	 *
	 * @since 0.4
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->register_hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @since 0.4
	 */
	public function register_hooks() {

		// reorder processors on save form
		add_filter( 'caldera_forms_presave_form', [ $this, 'reorder_contact_processors' ], 20 );

		/**
		 * The transients are set and destroyed twice, 
		 * one for the rendering of the form (autopopulation), 
		 * and another one for the form submission.
		 */
		
		// form render transient
		add_filter( 'caldera_forms_render_get_form', [ $this, 'set_form_transient' ], 1 );
		add_action( 'caldera_forms_render_end', [ $this, 'delete_form_transient' ], 1 );

		// form submission transient
		add_filter( 'caldera_forms_submit_get_form', [ $this, 'set_form_transient' ] );
		add_action( 'caldera_forms_submit_complete', [ $this, 'delete_form_transient' ] );
		
		// add CiviCRM panel
		if ( in_array( 'CiviContribute', $this->plugin->processors->enabled_components ) )  
			add_filter( 'caldera_forms_get_panel_extensions', [ $this, 'add_civicrm_tab' ], 10 );
		
		// use label in summary
		add_filter( 'caldera_forms_magic_summary_should_use_label', [ $this, 'summary_use_label' ], 10, 3 );
		// exclude hidden fields from summary
		add_filter( 'caldera_forms_summary_magic_fields', [ $this, 'exclude_hidden_fields_in_summary' ], 10, 2 );
		// render notices field
		add_action( 'caldera_forms_render_get_form', [ $this, 'render_notices_field' ], 30, 2 );

		// rebuild calculation field formula
		add_filter( 'caldera_forms_render_get_field', [ $this, 'rebuild_calculation_field_formula' ], 20, 2 );
		add_filter( 'caldera_forms_render_setup_field', [ $this, 'rebuild_calculation_field_formula' ], 20, 2 );

	}

	/**
	 * Set form transient.
	 * 
	 * @since 0.4.4
	 * @access public
	 * @param array $form The form config
	 * @return array $form The form config
	 */
	public function set_form_transient( $form ) {

		// bail if no processors
		if ( empty( $form['processors'] ) ) return $form;

		if ( Caldera_Forms::get_processor_by_type( 'civicrm_contact', $form ) )
			// set transient structure
			$this->set_transient_structure( $form );
		
		return $form;
	}

	/**
	 * Delete form transient.
	 * 
	 * @access public
	 * @since 0.4.4
	 */
	public function delete_form_transient() {
		return $this->plugin->transient->delete();
	}

	/**
	 * Transient structure.
	 *
	 * @since 0.4.4
	 * 
	 * @param array $form Form config
	 * @return array $form Form config
	 */
	public function set_transient_structure( $form ) {

		$structure = ( object ) [];
		$structure->contacts = ( object ) [];
		$structure->memberships = ( object ) [];
		$structure->participants = ( object ) [];
		$structure->events = ( object ) [];
		$structure->line_items = ( object ) [];
		$structure->orders = ( object ) [];

		array_map( function( $id, $processor ) use ( $structure, $form ) {

			if ( ! isset( $processor['runtimes'] ) ) return;
			// contacts
			if ( $processor['type'] == 'civicrm_contact' ) {
				$structure->contacts->$id = ( object ) [];
				// get current logged in/checksum contact if any
				$contact = $this->plugin->helper->current_contact_data_get();
				if ( $contact ) {
					// FIXME
					// revise use of 'processor_id' or 'cid_x'
					$structure->contacts->$id = $contact['contact_id'];
					$structure->contacts->{'cid_'.$processor['config']['contact_link']} = $contact['contact_id'];
				}
				return;
			}
			// memberships
			if ( $processor['type'] == 'civicrm_membership' ) {
				$structure->memberships->$id = ( object ) [];
				return;
			}
			// participants and events
			if ( $processor['type'] == 'civicrm_participant' ) {
				$structure->participants->$id = ( object ) [];
				// add events and corresponding event_id
				$structure->events->$id = ( object ) [];
				$structure->events->$id->event_id = $form['processors'][$id]['config']['id'];
				return;
			}
			// line items
			if ( $processor['type'] == 'civicrm_line_item' ) {
				$structure->line_items->$id = ( object ) [];
				return;
			}
			// orders
			if ( $processor['type'] == 'civicrm_order' ) {
				$structure->orders->$id = ( object ) [];
				return;
			}

		}, array_keys( $form['processors'] ), $form['processors'] );

		/**
		 * Transient structure, fires at form subsmission and at render time.
		 *
		 * @since 0.4.4
		 *
		 * @param object $structure The transient structure
		 * @param array $form Form config
		 */
		apply_filters( 'cfc_filter_transient_structure', $structure, $form );

		$this->plugin->transient->save( null, $structure );

		return $form;
	}

	/**
	 * Render notices field at the top of the form.
	 * @since 1.0
	 * @param array $form Form config
	 */
	public function render_notices_field( $form ) {
		/**
		 * Filter to replace the notices template.
		 * @since 1.0
		 * @var string $template_path The notices template path
		 */
		$template_path = apply_filters( 'cfc_notices_template_path', CF_CIVICRM_INTEGRATION_PATH . 'templates/notices.php', $form );

		$html = $this->plugin->html->generate( [ 'form' => $form ], $template_path );

		// mock html field
		$field = [
			'ID' => 'caldera_forms_civicrm_notices',
			'type' => 'html',
			'label' => 'caldera_forms_civicrm_notices',
			'slug' => 'caldera_forms_civicrm_notices',
			'conditions' => [
				'type' => ''
			],
			'caption' => '',
			'config' => [
				'custom_calss' => '',
				'default' => $html
			]
		];

		// add row placeholder for our new field at the begining of the structure
		$form['layout_grid']['structure'] = '12|' . $form['layout_grid']['structure'];

		// new grid, adjust field rows number
		$new_grid = array_map( function( $grid ) {

			$parts = explode( ':', $grid );
			$parts[0] = ++$parts[0];

			return implode( ':', $parts );

		}, $form['layout_grid']['fields'] );

		// new grid with the html field
		$form['layout_grid']['fields'] = array_merge( [ 'caldera_forms_civicrm_notices' => '1:1' ], $new_grid );
		// add field
		$form['fields']['caldera_forms_civicrm_notices'] = $field;

		return $form;

	}

	/**
	 * Add CiviCRM panel.
	 *
	 * @since 0.4.4
	 * 
	 * @param array $panels Panels
	 * @return array $panels Panels
	 */
	public function add_civicrm_tab( $panels ) {
		$panels['form_layout']['tabs'][ 'civicrm' ] = [
			'name' => __( 'CiviCRM', 'caldera-forms-civicrm' ),
			'label' => __( 'Caldera Forms CiviCRM', 'caldera-forms-civicrm' ),
			'location' => 'lower',
			'actions' => [],
			'side_panel' => null,
			'canvas' => CF_CIVICRM_INTEGRATION_PATH . 'panels/civicrm.php'
		];
		return $panels;
	}

	/**
	 * Use labels in summary.
	 *
	 * @since 0.4.4
	 * @param boolean $use Wether to use or not
	 * @param array $field Field config
	 * @param array $form Form config
	 * @return boolean $use
	 */
	public function summary_use_label( $use, $field, $form ) {
		
		if ( Caldera_Forms::get_processor_by_type( 'civicrm_contact', $form ) )
			return true;

		return $use;
	}

	/**
	 * Filter out hidden fields in summary.
	 *
	 * @since 0.4.4
	 *
	 * @param array $fields Fields in order they will be displayed
	 * @param array $form Form config
	 */
	public function exclude_hidden_fields_in_summary( $fields, $form ) {
		
		if ( Caldera_Forms::get_processor_by_type( 'civicrm_contact', $form ) )
			return array_filter( $fields, function( $field ) {
				return $field['type'] !== 'hidden';
			} );

		return $fields;
	}

	/**
	 * Reorder Contact processors, fires when a form is saved.
	 *
	 * @uses 'caldera_forms_presave_form' filter
	 * @since 0.4
	 *
	 * @param array $form The form
	 * @return array $form The modified form
	 */
	public function reorder_contact_processors( $form ) {
		// continue as normal if form has no processors
		if ( empty( $form['processors'] ) ) return $form;

		// contact processors
		$contacts = array_filter( $form['processors'], function( $processor ) {
			return $processor['type'] === 'civicrm_contact';
		} );
		// sort contact processors by contact_link
		uasort( $contacts, function( $a, $b ) {
			return $a['config']['contact_link'] - $b['config']['contact_link'];
		} );

		$form['processors'] = array_merge( $contacts, array_diff_assoc( $form['processors'], $contacts ) );

		return $form;
	}

	/**
	 * Rebuild calculation field formular.
	 *
	 * When fields are removed/hidden through 'caldera_forms_render_get_field' and
	 * 'caldera_forms_render_setup_field' filters, if the removed field is part of the 
	 * Calculation field formula, it breaks. The formula becomes ( 10+fld_123456 ).
	 *
	 * This method filters the calculation field to check for
	 * hidden/reomved fields and rebuild the formula.
	 *
	 * @since 1.0
	 * @param array $field The field config
	 * @param array $form The form config
	 * @return array $field The filtered field
	 */
	public function rebuild_calculation_field_formula( $field, $form ) {

		if ( $field['type'] != 'calculation' ) return $field;

		if ( ! isset( $field['config']['formular'] ) ) return $field;

		if ( ! isset( $field['config']['config']['group'] ) ) return $field;

		$do_group_lines = function( $lines ) use ( $form ) {

			$formula = '( ';

			foreach ( $lines as $line_id => $line ) {
				if ( ! empty( $form['fields'][$line['field']] ) )
					$formula .= ! $line_id ? $line['field']
						: ( is_numeric( substr( $formula, -1 ) ) ?
							$line['operator'] . $line['field']
							: $line['field']
						);
			}

			return $formula . ' )';

		};

		$formula = '';
		// rebuild formula
		foreach ( $field['config']['config']['group'] as $gid => $group ) {

			$formula .= ! $gid ?
				$do_group_lines( $group['lines'] )
				: ( isset( $group['operator'] ) ?
					' ' . $group['operator'] . ' '
					: $do_group_lines( $group['lines'] )
				);

		}

		$field['config']['formular'] = $formula;

		return $field;

	}
}