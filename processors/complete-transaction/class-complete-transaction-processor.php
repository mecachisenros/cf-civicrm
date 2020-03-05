<?php

class CiviCRM_Caldera_forms_Complete_transaction_Processor {

	/**
	 * @var \CiviCRM_Caldera_Forms
	 */
	public $plugin;

	public $key_name = 'civicrm_complete_transaction';

	/**
	 * CiviCRM_Caldera_forms_Recur_Contribution_Processor constructor.
	 *
	 * @param $plugin \CiviCRM_Caldera_Forms
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		// register this processor
		add_filter( 'caldera_forms_get_form_processors', [ $this, 'register_processor' ] );
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
			'name'           => __( 'CiviCRM Complete Transaction', 'cf-civicrm' ),
			'description'    => __( 'Complete transaction with Caldera form payment processor.', 'cf-civicrm' ),
			'author'         => 'Agileware',
			'template'       => CF_CIVICRM_INTEGRATION_PATH . 'processors/complete-transaction/complete_transaction_config.php',
			'pre_processor'  => [ $this, 'pre_processor' ],
			'processor'      => [ $this, 'processor' ],
			'post_processor' => [ $this, 'post_processor' ],
			'magic_tags'     => [],
		];

		return $processors;
	}

	/**
	 *
	 * @param array  $config    Processor configuration
	 * @param array  $form      Form configuration
	 * @param string $processid The process id
	 */
	public function pre_processor( $config, $form, $processid ) {

	}

	/**
	 * The recurring contribution is created here
	 *
	 * @param array  $config    Processor configuration
	 * @param array  $form      Form configuration
	 * @param string $processid The process id
	 */
	public function processor($config, $form, $processid) {
		global $transdata;
		$form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );
		$form_values = array_merge( $config, $form_values );

		if ( $form_values['contribution_status'] ) {
			$contribution = civicrm_api3('Contribution', 'completetransaction', [
				'id' => $form_values['contribution_id'],
				'trxn_id' => $form_values['trxn_id']
			]);
		} else {
			$contribution = civicrm_api3( 'Contribution', 'create', [
				'id' => $form_values['contribution_id'],
				'contribution_status_id' => "Failed",
				'trxn_id' => $form_values['trxn_id']
			]);
			// todo add the note to the contribution
			$transdata['note'] = "Contribution failed: " . $transdata['note'];
			$transdata['error'] = TRUE;
			return;
		}
		$contribution = array_shift( $contribution['values'] );
		if ( $config['create_recur'] ) {
			$this->createRcurringContribution( $form_values, $contribution );
		}
	}

	/**
	 * @param array  $config    Processor configuration
	 * @param array  $form      Form configuration
	 * @param string $processid The process id
	 */
	public function post_processor($config, $form, $processid) {

	}

	/**
	 * Create recurring contribution based on the first contribution
	 *
	 * @param $form_values array
	 * @param $baseContribution array
	 *
	 * @throws \CiviCRM_API3_Exception
	 */
	private function createRcurringContribution($form_values, $baseContribution) {
		$contributionRecur = [
			'contact_id' => $baseContribution['contact_id'],
			'amount' => $baseContribution['total_amount'],
			'financial_type_id' => $baseContribution['financial_type_id'],
			'contribution_status_id' => "In Progress",
		];

		foreach ($this->plugin->helper->contribution_recur_fields as $field) {
			$contributionRecur[$field] = $form_values[$field];
		}
		$contributionRecur['payment_token_id'] = $this->maybeSaveCustomerToken( $form_values, $baseContribution );

		// create recurring contribution
		$contributionRecur_result = civicrm_api3( 'ContributionRecur', 'create', $contributionRecur );
		$contributionRecur_result = array_shift( $contributionRecur_result['values'] );
		// calculate the next contribution date
		$next_sched = date('Y-m-d 00:00:00',
			strtotime("+{$contributionRecur_result['frequency_interval']} " .
			          "{$contributionRecur_result['frequency_unit']}s"));
		$contributionRecur_result['next_sched_contribution_date'] = $next_sched;
		$contributionRecur_result = civicrm_api3( 'ContributionRecur', 'create', $contributionRecur_result );
		// update the base contribution
		$baseContribution['contribution_recur_id'] = $contributionRecur_result['id'];
		civicrm_api3( 'Contribution', 'create', $baseContribution );
	}

	/**
	 * The input may be id or token, so convert it into id for creating recurring contribution
	 * Currently, it assume the input is an id, if there is no record, save it as a token
	 *
	 * @param $form_values array
	 * @param $contribution array
	 *
	 * @return mixed|string
	 * @throws \CiviCRM_API3_Exception
	 */
	private function maybeSaveCustomerToken( $form_values, $contribution ) {
		$id = $form_values['payment_token_id'];
		if ( empty( $id ) ) {
			return '';
		}
		// check if the id exist
		$tokenResult = civicrm_api3('PaymentToken', 'get', [
			'id' => $id
		]);

		if ( ! $tokenResult['count'] ) {
			// create new token
			$tokenResult = civicrm_api3( 'PaymentToken', 'create', [
				'contact_id' => $contribution['contact_id'],
				'payment_processor_id' => $form_values['payment_processor_id'],
				'token' => $id
			] );
			$id = $tokenResult['id'];
		}

		return $id;
	}
}