<?php

/**
 * CiviCRM Caldera Forms Entity Tag Processor Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Entity_Tag_Processor {

	/**
	 * The processor key.
	 *
	 * @since 0.2
	 * @access public
	 * @var str $key_name The processor key
	 */
	public $key_name = 'civicrm_entity_tag';

	/**
	 * Initialises this object.
	 *
	 * @since 0.2
	 */
	public function __construct() {

		// register this processor
		add_filter( 'caldera_forms_get_form_processors', array( $this, 'register_processor' ) );

	}

	/**
	 * Adds this processor to Caldera Forms.
	 *
	 * @since 0.2
	 *
	 * @uses 'caldera_forms_get_form_processors' filter
	 *
	 * @param array $processors The existing processors
	 * @return array $processors The modified processors
	 */
	public function register_processor( $processors ) {

		$processors[$this->key_name] = array(
			'name' => __( 'CiviCRM Tag', 'caldera-forms-civicrm' ),
			'description' => __( 'Add CiviCRM tags to contacts', 'caldera-forms-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/entity-tag/entity_tag_config.php',
			'processor' => array( $this, 'processor' ),
		);

		return $processors;

	}

	/**
	 * Form processor callback.
	 *
	 * @since 0.2
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 */
	public function processor( $config, $form ) {

		// globalised transient object
		global $transdata;

		foreach ( $config as $key => $value ) {
			if ( stristr( $key, 'entity_tag' ) != false ) {
				try {
					$tag = civicrm_api3( 'Tag', 'getsingle', array(
						'sequential' => 1,
						'id' => $value,
						'api.EntityTag.create' => array(
							'entity_id' => $transdata['civicrm']['contact_id_' . $config['contact_link']],
							'entity_table' => 'civicrm_contact',
							'tag_id' => '$value.id',
						),
					));
				} catch ( CiviCRM_API3_Exception $e ) {
					$error = $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>';
					return array( 'note' => $error, 'type' => 'error' );
				}
			}
		}

	}

}
