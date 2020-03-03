<?php

class CiviCRM_Caldera_forms_Recur_Contribution_Processor {

	/**
	 * @var \CiviCRM_Caldera_Forms
	 */
	public $plugin;

	public $key_name = 'civicrm_recur_contribution';

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
			'name'           => __( 'CiviCRM Recurring Contribution', 'cf-civicrm' ),
			'description'    => __( 'Create recurring contribution.', 'cf-civicrm' ),
			'author'         => 'Agileware',
			'template'       => CF_CIVICRM_INTEGRATION_PATH . 'processors/recur-contribution/recur_contribution_config.php',
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
		$contribution = new CRM_Contribute_BAO_Contribution();
		$contribution->id = $config['contribution_id'];
		$contribution->find(TRUE);
		$contributionRecur = [
			'contact_id' => $contribution->contact_id,
			'amount' => $contribution->total_amount,
			'financial_type_id' => $contribution->financial_type_id,
		];
		foreach ($this->plugin->helper->contribution_recur_fields as $field) {
			$contributionRecur[$field] = $form_values[$field];
		}
		// create recurring contribution
		$contributionRecur_result = civicrm_api3( 'ContributionRecur', 'create', $contributionRecur );
		$contributionRecur_result = array_shift( $contributionRecur_result['values'] );
		// calculate the next contribution date
		$next_sched = date('Y-m-d 00:00:00',
			strtotime("+{$contributionRecur_result['frequency_interval']} " .
			          "{$contributionRecur_result['frequency_unit']}s"));
		$contributionRecur_result['next_sched_contribution_date'] = $next_sched;
		civicrm_api3( 'ContributionRecur', 'create', $contributionRecur_result );
		// update the base contribution
		$contribution->contribution_recur_id = $contributionRecur_result['id'];
		//fixme transaction id?
		$contribution->contribution_status_id = "Completed";
		$contribution->save();
	}

	/**
	 * @param array  $config    Processor configuration
	 * @param array  $form      Form configuration
	 * @param string $processid The process id
	 */
	public function post_processor($config, $form, $processid) {

	}
}