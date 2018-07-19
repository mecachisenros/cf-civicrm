<?php

/**
 * CiviCRM Caldera Forms Assets Class
 *
 * @since 0.4.4
 */
class CiviCRM_Caldera_Forms_Assets {

	/**
     * Plugin reference.
     *
     * @since 0.4.4
     */
    public $plugin;

    /**
     * Initialises this object.
     *
     * @since 0.4.4
     */
    public function __construct( $plugin ) {
		$this->plugin = $plugin;
        $this->register_hooks();
    }

    /**
     * Register hooks.
     *
     * @since 0.4.4
     */
    public function register_hooks() {

        // enqueue scripts and js in form editor
        add_action( 'caldera_forms_admin_assets_scripts_registered', array( $this, 'enqueue_civicrm_scripts' ) );
		add_action( 'caldera_forms_admin_assets_styles_registered', array( $this, 'enqueue_civicrm_styles' ) );

    }

	/**
	 * Enqueue scripts.
	 *
	 * @uses 'caldera_forms_admin_assets_scripts_registered' action
	 * @since 0.4.2
	 */
	public function enqueue_civicrm_scripts(){
        // dequeue if we are not in the form editor
        if( ! $this->is_caldera_forms_admin() )
            wp_dequeue_script( 'cfc-select2' );
		// select2 4.0.3 script with tiny hack to register our own name and prevent conflicts
        if( $this->is_caldera_forms_admin() ) {
			wp_enqueue_script( 'cfc-select2', CF_CIVICRM_INTEGRATION_URL . 'assets/js/select2.js', array( 'jquery' ), CF_CIVICRM_INTEGRATION_VER );
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
        if( ! $this->is_caldera_forms_admin() )
            wp_dequeue_style( 'cfc-select2' );
		// select2 4.0.3 style
        if( $this->is_caldera_forms_admin() )
            wp_enqueue_style( 'cfc-select2', CF_CIVICRM_INTEGRATION_URL . 'assets/css/select2.min.css', array(), CF_CIVICRM_INTEGRATION_VER );

    }
    
    /**
     * Check if are in Caldera Forms admin context.
     * 
     * @since 0.4.4
     */
    public function is_caldera_forms_admin() {
        return is_admin() && isset( $_GET['page'] ) && $_GET['page'] == 'caldera-forms';
    }
}