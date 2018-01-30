<?php

/**
 * CiviCRM Caldera Forms Forms Class
 *
 * @since 0.4
 */
class CiviCRM_Caldera_Forms_Forms {

    /**
     * Initialises this object.
     *
     * @since 0.4
     */
    public function __construct() {
        $this->register_hooks();
    }

    /**
     * Register hooks.
     *
     * @since 0.4
     */
    public function register_hooks() {

        // reorder processors on save form
        add_filter( 'caldera_forms_presave_form', array( $this, 'reorder_contact_processors' ), 20 );
        // enqueue scripts and js in form editor
        add_action( 'caldera_forms_admin_assets_scripts_registered', array( $this, 'enqueue_civicrm_scripts' ) );
		add_action( 'caldera_forms_admin_assets_styles_registered', array( $this, 'enqueue_civicrm_styles' ) );

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
		if( empty( $form['processors'] ) ) return $form;

		$contact_processors = $rest_processors = array();
		foreach ( $form['processors'] as $pId => $processor ) {
			if( $processor['type'] == 'civicrm_contact' ){
				$contact_processors[$pId] = $processor;
			}
			if( $processor['type'] != 'civicrm_contact' ){
				$rest_processors[$pId] = $processor;
			}
		}

		// Sort Contact processors based on Contact Link
		uasort( $contact_processors, function( $a, $b ){
            return $a['config']['contact_link'] - $b['config']['contact_link'];
        });

		$form['processors'] = array_merge( $contact_processors, $rest_processors);

		return $form;
	}

	/**
	 * Enqueue scripts.
	 *
	 * @uses 'caldera_forms_admin_assets_scripts_registered' action
	 * @since 0.4.2
	 */
	public function enqueue_civicrm_scripts(){
        // dequeue if we are not in the form editor
        if( ! is_admin() && ! isset( $_GET['page'] ) && $_GET['page'] != 'caldera-forms' )
            wp_dequeue_script( 'civicrm-select2' );
		// select2 4.0.3 script with tiny hack to register our own name and prevent conflicts
        if( is_admin() && isset( $_GET['page'] ) && $_GET['page'] == 'caldera-forms' ) {
			wp_enqueue_script( 'civicrm-select2', CF_CIVICRM_INTEGRATION_URL . 'assets/js/select2.js', array( 'jquery' ), CF_CIVICRM_INTEGRATION_VER );
			wp_enqueue_script( 'cfc-admin', CF_CIVICRM_INTEGRATION_URL . 'assets/js/admin.js', array( 'jquery' ), CF_CIVICRM_INTEGRATION_VER );
        }
	}

	/**
	 * Enqueue styles.
	 *
	 * @uses 'caldera_forms_admin_assets_scripts_registered' action
	 * @since 0.4.2
	 */
	public function enqueue_civicrm_styles(){
        // dequeue if we are not in the form editor
        if( ! is_admin() && ! isset( $_GET['page'] ) && $_GET['page'] != 'caldera-forms' )
            wp_dequeue_style( 'civicrm-select2' );
		// select2 4.0.3 style
        if( is_admin() && isset( $_GET['page'] ) && $_GET['page'] == 'caldera-forms' )
            wp_enqueue_style( 'civicrm-select2', CF_CIVICRM_INTEGRATION_URL . 'assets/css/select2.min.css', array(), CF_CIVICRM_INTEGRATION_VER );

	}
}
