<?php

/**
 * CiviCRM Caldera Forms Membership Processor Class.
 *
 * @since 0.4.4
 */
class CiviCRM_Caldera_Forms_Membership_Processor {

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
	 * @since 0.4.4
	 * @access public
	 * @var str $key_name The processor key
	 */
	public $key_name = 'civicrm_membership';

	/**
	 * Initialises this object.
	 *
	 * @since 0.4.4
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		// register this processor
		add_filter( 'caldera_forms_get_form_processors', [ $this, 'register_processor' ] );
		// filter form before rendering
		// add_filter( 'caldera_forms_render_get_form', [ $this, 'pre_render' ] );

	}

	/**
	 * Adds this processor to Caldera Forms.
	 *
	 * @since 0.4.4
	 *
	 * @uses 'caldera_forms_get_form_processors' filter
	 *
	 * @param array $processors The existing processors
	 * @return array $processors The modified processors
	 */
	public function register_processor( $processors ) {

		$processors[$this->key_name] = [
			'name' =>  __( 'CiviCRM Membership', 'caldera-forms-civicrm' ),
			'description' =>  __( 'Create/Renew CiviCRM Memberhips.', 'caldera-forms-civicrm' ),
			'author' =>  'Andrei Mondoc',
			'template' =>  CF_CIVICRM_INTEGRATION_PATH . 'processors/membership/membership_config.php',
			'pre_processor' =>  [ $this, 'pre_processor' ],
			'processor' => [ $this, 'processor' ],
			'magic_tags' => [ 'processor_id' ],
		];

		return $processors;

	}

	/**
	 * Form pre-processor callback.
	 *
	 * @since 0.4.4
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 * @param string $processid The process id
	 */
	public function pre_processor( $config, $form, $processid ) {

		global $transdata;

		// cfc transient object
		$transient = $this->plugin->transient->get();
		$this->contact_link = 'cid_' . $config['contact_link'];

		// is member?
		try {
			
			$is_member = civicrm_api3( 'Membership', 'getsingle', [
				'contact_id' => $transient->contacts->{$this->contact_link},
				'membership_type_id' => $config['membership_type_id'], 
			] );

		} catch ( CiviCRM_API3_Exception $e ) {
				// ignore
		}

		if ( isset( $is_member ) && ! $member['is_error'] && ! isset( $config['is_renewal'] ) )
			return [
				'note' => sprintf( __( 'It looks like you already have a %1$s membership.', 'caldera-forms-civicrm' ), $is_member['membership_name'] ), 
				'type' => 'error'
			];
			
		// Get form values
		$form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );
		
		if ( ! empty( $transient->contacts->{$this->contact_link} ) ) {

			$form_values['contact_id'] = $transient->contacts->{$this->contact_link};

			// renew/extend necessary params
			if ( isset( $config['is_renewal'] ) && isset( $is_member['id'] ) ) {
				$form_values['id'] = $is_member['id'];
				// at least one term
				$form_values['num_terms'] = ! empty( $form_values['num_terms'] ) ? $form_values['num_terms'] : 1;
			}

			if ( isset( $config['is_price_field_based'] ) )
				$form_values['is_price_field_based'] = $config['is_price_field_based'];

			$form_values['membership_type_id'] = $config['membership_type_id'];
			$form_values['source'] = isset( $form_values['source'] ) ? $form_values['source'] : $form['name'];

			if ( ! $config['is_renewal'] ) {
				$form_values['join_date'] = ! empty( $form_values['join_date'] ) ? $form_values['join_date'] : date('Y-m-d');
				$form_values['start_date'] = ! empty( $form_values['start_date'] ) ? $form_values['start_date'] : date('Y-m-d');
			}
 
			$transient->memberships->{$config['processor_id']}->params = $form_values;
			slack( [ 'member_values' => $form_values ] );
			$this->plugin->transient->save( $transient->ID, $transient );

			// free memberships
			if( ! $config['is_monetary'] ) {
				unset( $form_values['is_price_field_based'] );
				try {
					$create_member = civicrm_api3( 'Membership', 'create', $form_values );
				} catch ( CiviCRM_API3_Exception $e ) {
					$error = $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>';
					return [ 'note' => $error, 'type' => 'error' ];
				}
			}
		}
	}

	/**
	 * Form processor callback.
	 *
	 * @since 0.4.4
	 * 
	 * @param array $config Processor configuration 
	 * @param array $form Form configuration
	 * @param string $processid The process id (it may not be set)
	 */
	public function processor( $config, $form, $processid ) {
		return ['processor_id' => $config['processor_id'] ];
	}

}
