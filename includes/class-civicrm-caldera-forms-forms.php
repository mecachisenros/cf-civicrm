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

		add_filter( 'caldera_forms_submit_get_form', [ $this, 'set_transient_structure' ] );
		/**
		 * The transients are set and destroyed twice, 
		 * one for the rendering of the form (autopopulation), 
		 * and another one for the form submission.
		 */
		// form render transient
		add_filter( 'caldera_forms_render_get_form', [ $this, 'set_render_transient' ], 5 );
		add_action( 'caldera_forms_render_end', [$this, 'delete_render_transient'] );

		// form submission transient
		// add_action( 'caldera_forms_submit_process_before', [$this, 'set_transient'] );
		add_action( 'caldera_forms_submit_complete', [ $this, 'delete_transient' ] );

	}

	public function set_transient_structure( $form ) {

		$transient = new stdClass();

		foreach ( $form['processors'] as $id => $processor ) {
			if ( isset( $processor['runtimes'] ) ) {
				if ( $processor['type'] == 'civicrm_contact' ) {
					$transient->contacts->$id = new stdClass();
				}
				if ( $processor['type'] == 'civicrm_membership' ) {
					$transient->memberships->$id = new stdClass();
				}
				if ( $processor['type'] == 'civicrm_participant' ) {
					$transient->participants->$id = new stdClass();
				}
				if ( $processor['type'] == 'civicrm_order' ) {
					$transient->orders->$id = new stdClass();
				}
				if ( $processor['type'] == 'civicrm_line_item' ) {
					$transient->line_items->$id = new stdClass();
				}
			}
		}

		$transient->ID = $transient_id = $this->transient_id = $this->plugin->transient->unique_id();

		$this->plugin->transient->save( $transient_id, $transient );

		return $form;
	}
	
	/**
	 * Set transient at the begining of form processing.
	 * 
	 * @since 0.4.4
	 * @access public
	 * @param array $form The form config
	 * @return array $form The form config
	 */
	public function set_render_transient( $form ) {
		// $this->set_transient_structure( $form );
		// bail if no processors
		if ( empty( $form['processors'] ) ) return $form;

		if ( Caldera_Forms::get_processor_by_type( 'civicrm_contact', $form ) )
			$is_contact_processor = true;

		// set transient if there's a contact processor
		if ( isset( $is_contact_processor ) ) $this->set_transient();
		
		return $form;
	}
	
	/**
	 * Delete transient after form renders.
	 * 
	 * @access public
	 * @since 0.4.4
	 */
	public function delete_render_transient( $form ) {
		return $this->delete_transient();
	}

	/**
	 * Set transient at the begining of form processing.
	 * 
	 * @since 0.4.4
	 * @access public
	 */
	public function set_transient() {
		// set unique id
		$transient_id = $this->transient_id = $this->plugin->transient->unique_id();
		// set transient
		$data = new stdClass;
		$data->ID = $transient_id;
		$data->contacts = new stdClass();

		return $this->plugin->transient->save( $transient_id, $data );
	}

	/**
	 * Delete transient after submission.
	 * 
	 * @access public
	 * @since 0.4.4
	 */
	public function delete_transient() {
		return $this->plugin->transient->delete();
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