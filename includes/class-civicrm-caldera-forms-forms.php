<?php

/**
 * CiviCRM Caldera Forms Forms Class
 *
 * @since 0.4
 */
class CiviCRM_Caldera_Forms_Forms {

    /**
     * Initialises this object
     *
     * @since 0.4
     */
    public function __construct() {
        $this->register_hooks();
    }

    /**
     * Register hooks
     *
     * @since 0.4
     */
    public function register_hooks() {

        // reorder processors on save form
        add_filter( 'caldera_forms_presave_form', array( $this, 'reorder_contact_processors' ), 20 );

    }

    /**
	 * Reorder Contact processors, fires when a form is saved
	 *
	 * @uses 'caldera_forms_presave_form' filter
	 * @since 0.4
	 *
	 * @param array $form The form
	 * @return array $form The modified form
	 */
	public function reorder_contact_processors( $form ) {

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
}
