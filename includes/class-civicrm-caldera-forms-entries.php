<?php

/**
 * CiviCRM Caldera Forms Entries Class.
 *
 * @since 0.4.2
 */
class CiviCRM_Caldera_Forms_Entries {

	/**
	 * Initialises this object.
	 *
	 * @since 0.4.2
	 */
	public function __construct() {

		// register Caldera Forms callbacks
		$this->register_hooks();

	}

	/**
	 * Register hooks.
	 *
	 * @since 0.4.2
	 */
	public function register_hooks() {

		add_filter( 'caldera_forms_get_entry', array( $this, 'get_entry' ), 10, 3 );

	}

	public function get_entry( $data, $entry_id, $form ) {

		foreach ( $data['data'] as $field_id => $values ) {
			$field = Caldera_Forms_Field_Util::get_field( $field_id, $form );
			if ( ! empty( $values['value'] ) && $field['type'] == 'file' && isset( $field['config']['civicrm_file_upload'] ) ) {
				try {
					$attachment = CiviCRM_Caldera_Forms_Helper::try_crm_api('Attachment', 'getsingle', array(
  						'id' => $values['value'],
					));
				} catch (Exception $e) {

				}
				if ( isset( $attachment ) && ! $attachment['is_error'] ) {
					$data['data'][$field_id]['view'] = '<a href="' . $attachment['url'] . '" target="_blank">' . $attachment['name'] . '</a>';
				}
				unset( $attachment );
			}

		}

		return $data;
	}
}
