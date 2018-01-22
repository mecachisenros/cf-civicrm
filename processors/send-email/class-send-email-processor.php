<?php
/**
 * CiviCRM Caldera Forms Send Email Processor Class.
 *
 * @since 0.4.1
 */
class CiviCRM_Caldera_Forms_Send_Email_Processor {

	/**
	 * The processor key.
	 *
	 * @since 0.4.1
	 * @access public
	 * @var str $key_name The processor key
	 */
	public $key_name = 'civicrm_send_email';

	/**
	 * Initialises this object.
	 *
	 * @since 0.4.1
	 */
	public function __construct() {

		// register this processor
		add_filter( 'caldera_forms_get_form_processors', array( $this, 'register_processor' ) );

	}

	/**
	 * Adds this processor to Caldera Forms.
	 *
	 * @since 0.4.1
	 *
	 * @uses 'caldera_forms_get_form_processors' filter
	 *
	 * @param array $processors The existing processors
	 * @return array $processors The modified processors
	 */
	public function register_processor( $processors ) {

		$processors[$this->key_name] = array(
			'name' => __( 'CiviCRM Send Email', 'caldera-forms-civicrm' ),
			'description' => __( 'Send Email from CiviCRM (CiviCRM message templates, requires Email API)', 'caldera-forms-civicrm' ),
			'author' => 'Andrei Mondoc',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/send-email/send_email_config.php',
			'pre_processor' =>  array( $this, 'pre_processor' ),
		);

		return $processors;

	}

	/**
	 * Form processor callback.
	 *
	 * @since 0.4.1
	 *
	 * @param array $config Processor configuration
	 * @param array $form Form configuration
	 */
	public function pre_processor( $config, $form ) {

		// globalised transient object
		global $transdata;

		$params = array(
			'sequential' => 1,
			'contact_id' => $transdata['civicrm']['contact_id_' . $config['contact_link']],
			'template_id' => $config['template_id'],
		);

		if ( isset( $config['from_name'] ) && isset( $config['from_email'] ) ) {
			$params['from_name'] = $config['from_name'];
			$params['from_email'] = $config['from_email'];
		}

		if ( isset( $config['alternative_receiver_address'] ) ) {
			$params['alternative_receiver_address'] = $config['alternative_receiver_address'];
		}

		try {
			$send_email = civicrm_api3( 'Email', 'send', $params );
		} catch ( CiviCRM_API3_Exception $e ) {
			$error = $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>';
			return array( 'note' => $error, 'type' => 'error' );
		}
	}
}
