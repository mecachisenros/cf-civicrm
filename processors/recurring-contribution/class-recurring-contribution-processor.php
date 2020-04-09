<?php

/**
 * CiviCRM Caldera Forms Email Processor Class.
 *
 * @since 0.2
 */
class CiviCRM_Caldera_Forms_Recurring_Contribution_Processor {

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
	public $key_name = 'civicrm_recurring_contribution';

	/**
	 * Fields to ignore while prepopulating
	 *
	 * @since 0.4
	 * @access public
	 * @var array $fields_to_ignore Fields to ignore
	 */
	public $fields_to_ignore = [ 'contact_link', 'location_type_id' ];

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
			'name' => __( 'CiviCRM Recurring Contribution', 'cf-civicrm' ),
			'description' => __( 'Create a recurring contribution', 'cf-civicrm' ),
			'author' => 'Agileware',
			'template' => CF_CIVICRM_INTEGRATION_PATH . 'processors/recurring-contribution/recurring_contribution_config.php',
			'pre_processor' => [ $this, 'pre_processor' ],
			'processor' => [ $this, 'processor', ]
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
		$form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );
		$form_values = array_merge( $config, $form_values );
		try {
			$contribution = civicrm_api3( 'Contribution',
				'getsingle',
				[
					'id' => $form_values['contribution_id'],
				] );
		} catch ( CiviCRM_API3_Exception $e ) {
			return;
		}
		$recurContribution = $this->createRcurringContribution( $form_values, $contribution );
	}

	public function processor( $config, $form, $processid ) {
		$form_values = $this->plugin->helper->map_fields_to_processor( $config, $form, $form_values );
		if ( $form_values['contribution_id'] && $form_values['payment_token_id'] ) {
			$this->activiateRecurringContribution( $form_values['contribution_id'], $form_values['payment_token_id'] );
		}
	}

	/**
	 * Create recurring contribution based on the first contribution
	 *
	 * @param $form_values array
	 * @param $baseContribution array
	 */
	private function createRcurringContribution($form_values, $baseContribution) {
		$contributionRecur = [
			'contact_id' => $baseContribution['contact_id'],
			'amount' => $baseContribution['total_amount'],
			'financial_type_id' => $baseContribution['financial_type_id'],
		];
		$contributionRecur['contribution_status_id'] = $baseContribution['contribution_status_id'];

		foreach ($this->plugin->helper->contribution_recur_fields as $field) {
			$contributionRecur[$field] = $form_values[$field];
		}
		// in the pre_processor, new payment token is not ready yet
		unset( $contributionRecur['payment_token_id'] );

		// create recurring contribution
		try {
			$contributionRecur_result = civicrm_api3( 'ContributionRecur', 'create', $contributionRecur );
		} catch ( CiviCRM_API3_Exception $e ) {
			return;
		}
		$contributionRecur_result = array_shift( $contributionRecur_result['values'] );
		// update the base contribution
		$baseContribution['contribution_recur_id'] = $contributionRecur_result['id'];
		try {
			civicrm_api3( 'Contribution', 'create', $baseContribution );

			self::processLineItems($baseContribution);
		} catch ( CiviCRM_API3_Exception $e ) {
			return;
		}

		return $contributionRecur_result;
	}

	/**
	 * Since the status and the next billing date will be automatically updated if the contribution is
	 * set up correctly. We just need to update the payment token here.
	 * We update the token later because the recurring is created on pre_processor and the token processor
	 * may not ready yet.
	 * @param $id string|int
	 */
	private function activiateRecurringContribution($id, $tokenID) {
		try{
			$contributionRecur_result = civicrm_api3('ContributionRecur', 'getsingle', [
				'id' => $id
			]);
		} catch ( CiviCRM_API3_Exception $e ) {
			return;
		}
		$contributionRecur_result['payment_token_id'] = $tokenID;
		try {
			$contributionRecur_result = civicrm_api3( 'ContributionRecur', 'create', $contributionRecur_result );
		} catch ( CiviCRM_API3_Exception $e ) {
		}
	}

	/**
	 * Adds the contribution_recur_id to applicable entities ( Membership )
	 *
	 * @param $contribution - The contribution to copy the contribution_recur_id from
	 *
	 * @returns int - The number of updated line items.
	 *
	 * @throws CiviCRM_API3_Exception.
	 */
	private static function processLineItems($contribution) {
		if ( empty( $contribution['contribution_recur_id'] ) ) {
			// There's no point if we don't have this.
			return;
		}

		$n = 0;

		// Check each line item for an entity that can recur
		foreach ( civicrm_api3( 'LineItem', 'get', [ 'contribution_id' => $contribution['id'], 'sequential' => 1 ] )['values'] as $line_item ) {
			switch ( $line_item['entity_table'] ) {
				case 'civicrm_membership':
					// Can the linked membership auto-renew?
					$auto_renewal = civicrm_api3( 'Membership', 'getvalue', [
						'id' => $line_item['entity_id'],
						'return' => 'membership_type_id.auto_renew'
					] );

					// 0 means no, 1 or 2 for optional or mandatory respectively - assume they opted in if they got this far.
					if( $auto_renew ) {
						civicrm_api3( 'Membership', 'create', [
							'id' => $line_item['entity_id'],
							'contribution_recur_id' => $contribution['contribution_recur_id'],
						]);

						$n++;
					}
					break;

				default:
					break;
			}
		}

		return $n;
	}
}
