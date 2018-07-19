<?php

/**
 * CiviCRM Caldera Forms Entity Tag Processor Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Entity_Tag_Processor {

	/**
     * Plugin reference.
     *
     * @since 0.4.4
	 * @access public
	 * @var object $plugin The plugin instance
     */
	public $plugin;
	
	/**
	 * Contact link.
	 * 
	 * @since 0.4.4
	 * @access protected
	 * @var string $contact_link The contact link
	 */
	protected $contact_link;

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
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		// register this processor
		add_filter( 'caldera_forms_get_form_processors', [ $this, 'register_processor' ] );

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

		$processors[$this->key_name] = [
			'name' => __( 'CiviCRM Tag', 'caldera-forms-civicrm' ),
			'description' => __( 'Add CiviCRM tags to contacts', 'caldera-forms-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/entity-tag/entity_tag_config.php',
			'pre_processor' => [ $this, 'pre_processor' ],
		];

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
	public function pre_processor( $config, $form, $processid ) {

		// cfc transient object
		$transient = $this->plugin->transient->get();
		$this->contact_link = 'cid_' . $config['contact_link'];

		foreach ( $config as $key => $value ) {
			if ( stristr( $key, 'entity_tag' ) != false ) {
				try {
					$tag = civicrm_api3( 'Tag', 'getsingle', [
						'sequential' => 1,
						'id' => $value,
						'api.EntityTag.create' => [
							'entity_id' => $transient->contacts->{$this->contact_link},
							'entity_table' => 'civicrm_contact',
							'tag_id' => '$value.id',
						],
					] );
				} catch ( CiviCRM_API3_Exception $e ) {
					$error = $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>';
					return [ 'note' => $error, 'type' => 'error' ];
				}
			}
		}

	}

}
