<?php

class CiviCRM_Caldera_forms_Payment_Token_Processor {

	/**
	 * @var \CiviCRM_Caldera_Forms
	 */
	public $plugin;

	public $key_name = 'civicrm_payment_token';
	/**
	 * @var string
	 */
	private $contact_link;

	/**
	 * CiviCRM_Caldera_forms_Recur_Contribution_Processor constructor.
	 *
	 * @param $plugin \CiviCRM_Caldera_Forms
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		// register this processor
		add_filter( 'caldera_forms_get_form_processors', [ $this, 'register_processor' ] );
		// filter form before rendering
		add_action( 'caldera_forms_autopopulate_types', [ $this, 'auto_fields_types' ] );
		add_filter( 'caldera_forms_render_get_field', [ $this, 'auto_fields_values' ], 10, 2 );
	}

	/**
	 *
	 * @param array $processors The existing processors
	 *
	 * @return array $processors The modified processors
	 * @uses 'caldera_forms_get_form_processors' filter
	 *
	 */
	public function register_processor( $processors ) {

		$processors[ $this->key_name ] = [
			'name'          => __( 'CiviCRM Payment Token', 'cf-civicrm' ),
			'description'   => __( 'Working with the payment token entity.', 'cf-civicrm' ),
			'author'        => 'Agileware',
			'template'      => CF_CIVICRM_INTEGRATION_PATH . 'processors/payment-token/payment_token_config.php',
			'pre_processor' => [ $this, 'pre_processor' ],
			'processor'     => [ $this, 'processor' ],
			'magic_tags'    => [
				'token_id',
				'token',
			],
		];

		return $processors;
	}

	/**
	 * @see caldera_forms_autopopulate_types
	 */
	public function auto_fields_types() {
		echo "<option value=\"payment_token_id\"{{#is auto_type value=\"payment_token_id\"}} selected=\"selected\"{{/is}}>"
		     . __( 'CiviCRM - Payment Token', 'cf-civicrm' ) . "</option>";
	}

	/**
	 * Form processor callback.
	 *
	 * @param array $config Processor configuration
	 * @param array $form   Form configuration
	 *
	 * @since 0.2
	 *
	 */
	public function pre_processor( $config, $form, $processid ) {
		// Get form values
		$form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );
		/** We will try to get the meta values in the pre_processor.
		 * This is only possible when the token id is provided.
		 */
		if ( ! $form_values['id'] ) {
			return NULL;
		}

		try {
			$token = civicrm_api3( 'PaymentToken',
				'getsingle',
				[
					'id' => $form_values['id'],
				] );
		} catch ( CiviCRM_API3_Exception $e ) {
			return $this->error( 'The given token ID is wrong.' );
		}
		Caldera_Forms::set_submission_meta( 'token_id', $token['id'], $form, $config['processor_id'] );
		Caldera_Forms::set_submission_meta( 'token', $token['token'], $form, $config['processor_id'] );
	}

	public function processor( $config, $form, $processid ) {
		$form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );
		// delete token action
		if ( $config['is_delete'] ) {
			// must have the id set
			if ( ! $form_values['id'] ) {
				return NULL;
			}
			try{
				$result = civicrm_api3( 'PaymentToken',
					'delete',
					[
						'id' => $form_values['id'],
					] );
			} catch ( CiviCRM_API3_Exception $e ) {
			}

			return NULL;
		}

		// cfc transient object
		$transient = $this->plugin->transient->get();
		// contact id
		$this->contact_link        = 'cid_' . $config['contact_link'];
		$contactID                 = $transient->contacts->{$this->contact_link} ?? NULL;
		$form_values['contact_id'] = $contactID;

		$form_values = array_filter($form_values + $config);

		unset($form_values['process_id'], $form_values['contact_link']);

		if ( empty( $form_values['id'] ) ) {
			// create new token
			if ( empty( $form_values['payment_processor_id'] )
			     || empty( $form_values['token'] )
			     || empty( $form_values['contact_id'] )
			) {
				return NULL;
			}
			// dedupe based on the required fields
			try {
				$result = civicrm_api3( 'PaymentToken',
					'get',
					[
						'payment_processor_id' => $form_values['payment_processor_id'],
						'token'                => $form_values['token'],
						'contact_id'           => $form_values['contact_id'],
					] );
				if ( ! empty( $result['count'] ) ) {
					$form_values['id'] = array_shift( $result['values'] )['id'];
				}
			} catch ( CiviCRM_API3_Exception $e ) {
				// keep going
			}
		}

		try {
			$result = civicrm_api3( 'PaymentToken', 'create', $form_values );
			// Pass magic tags value
			$token = array_shift( $result['values'] );

			return [
				'token_id' => $token['id'],
				'token'    => $token['token'],
			];
		} catch ( CiviCRM_API3_Exception $e ) {

		}
	}

	/**
	 * build the payment token dropdown
	 *
	 * @see caldera_forms_render_get_field
	 */
	public function auto_fields_values( $field, $form ) {
		// fixme implement
		if ( ! $field['config']['auto'] && $field['config']['auto_type'] !== 'payment_token_id' ) {
			return $field;
		}
		$processor = NULL;
		foreach ( $form['processors'] as $processor => $pr_id ) {
			if ( $pr_id['type'] === $this->key_name ) {
				$processor = $pr_id;
				break;
			}
		}
		if ( ! $processor ) {
			return $field;
		}
		// the linked contact id
		$transient          = $this->plugin->transient->get();
		$this->contact_link = 'cid_' . $processor['config']['contact_link'];
		$contactID          = $transient->contacts->{$this->contact_link} ?? NULL;
		if ( ! $contactID ) {
			return $field;
		}
		try {
			$result = civicrm_api3( 'PaymentToken',
				'get',
				[
					'contact_id' => $contactID,
				]
			);
		} catch ( CiviCRM_API3_Exception $e ) {
			return $field;
		}
		if ( $result['is_error'] ) {
			return $field;
		}
		// adding tokens to option
		foreach ( $result['values'] as $value ) {
			$field['config']['option'][] = [
				'value' => $value['id'],
				'label' => $value['masked_account_number'],
			];
		}

		return $field;
	}

	/**
	 * TODO move this helper method
	 *
	 * @param $message string
	 *
	 * @return array
	 */
	private function error( $message ) {
		// the error format for a pre_processor
		return [
			'type' => 'error',
			'note' => $message,
		];
	}
}