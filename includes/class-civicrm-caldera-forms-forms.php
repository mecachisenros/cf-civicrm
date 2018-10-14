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
		add_action( 'caldera_forms_render_end', [ $this, 'delete_form_transient' ] );

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

		$has_contact_processor = false;

		if ( Caldera_Forms::get_processor_by_type( 'civicrm_contact', $form ) )
			$has_contact_processor = true;

		// set transient structure
		if ( $has_contact_processor ) $this->set_transient_structure( $form );
		
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

		$structure = new stdClass();

		foreach ( $form['processors'] as $id => $processor ) {
			if ( isset( $processor['runtimes'] ) ) {
				if ( $processor['type'] == 'civicrm_contact' ) {
					$structure->contacts = new stdClass();
					$structure->contacts->$id = new stdClass();
				}

				if ( $processor['type'] == 'civicrm_membership' ) {
					$structure->memberships = new stdClass();
					$structure->memberships->$id = new stdClass();
				}

				if ( $processor['type'] == 'civicrm_participant' ) {
					$structure->participants = new stdClass();
					$structure->participants->$id = new stdClass();
				}

				if ( $processor['type'] == 'civicrm_order' ) {
					$structure->orders = new stdClass();
					$structure->orders->$id = new stdClass();
				}

				if ( $processor['type'] == 'civicrm_line_item' ) {
					$structure->line_items = new stdClass();
					$structure->line_items->$id = new stdClass();
				}

			}
		}

		/**
		 * Transient structure, fires at form subsmission and at render time.
		 *
		 * @since 0.4.4
		 *
		 * @param object $transient The transient stricture
		 * @param array $form Form config
		 */
		apply_filters( 'cfc_filter_transient_structure', $transient, $form );

		$this->plugin->transient->save( null, $structure );

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
		
		if( Caldera_Forms::get_processor_by_type( 'civicrm_contact', $form ) )
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
		if( Caldera_Forms::get_processor_by_type( 'civicrm_contact', $form ) ) {
			foreach ( $fields as $id => $field ) {
				if ( $field['type'] == 'hidden' ) {
					unset( $fields[$id] );
				}
			}
		}
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

		$contact_processors = $rest_processors = [];
		foreach ( $form['processors'] as $pId => $processor ) {
			if ( $processor['type'] == 'civicrm_contact' ) {
				$contact_processors[$pId] = $processor;
			}
			if ( $processor['type'] != 'civicrm_contact' ) {
				$rest_processors[$pId] = $processor;
			}
		}

		// Sort Contact processors based on Contact Link
		uasort( $contact_processors, function( $a, $b ) {
            return $a['config']['contact_link'] - $b['config']['contact_link'];
        });

		$form['processors'] = array_merge( $contact_processors, $rest_processors );

		return $form;
	}
}