<?php
/**
 * CiviCRM Caldera Forms - Organisation starter template.
 *
 * @since 0.3
 */

return array(
	'name' => __( 'CiviCRM Organisation', 'caldera-forms-civicrm' ),
	'description' => __( 'Basic CiviCRM organisation form.', 'caldera-forms-civicrm' ),
	'db_support' => 1,
	'hide_form' => 1,
	'check_honey' => 1,
	'success' => __( 'Form has been successfully submitted. Thank you.', 'caldera-forms-civicrm' ),
	'avatar_field' => '',
	'form_ajax' => 1,
	'layout_grid' =>
	array(
		'fields' =>
		array(
			'organisation_name' => '1:1',
			'email' => '1:1',
			'street_address' => '2:1',
			'supplemental_address' => '2:1',
			'city' => '2:1',
			'state' => '3:1',
			'country' => '3:2',
			'submit' => '4:1',
		),
		'structure' => '12|12|6:6|12',
	),
	'fields' =>
	array(
		'organisation_name' =>
		array(
			'ID' => 'organisation_name',
			'type' => 'text',
			'label' => __( 'Organisation Name', 'caldera-forms-civicrm' ),
			'slug' => 'organisation_name',
			'conditions' =>
			array(
				'type' => '',
			),
			'required' => 1,
			'caption' => '',
			'config' =>
			array(
				'custom_class' => '',
				'placeholder' => '',
				'default' => '',
				'mask' => '',
				'type_override' => 'text',
			),
		),
		'email' =>
		array(
			'ID' => 'email',
			'type' => 'email',
			'label' => __( 'Contact Email', 'caldera-forms-civicrm' ),
			'slug' => 'email',
			'conditions' =>
			array(
				'type' => '',
			),
			'required' => 1,
			'caption' => '',
			'config' =>
			array(
				'custom_class' => '',
				'placeholder' => '',
				'default' => '',
			),
		),
		'street_address' =>
		array(
			'ID' => 'street_address',
			'type' => 'text',
			'label' => __( 'Street Address', 'caldera-forms-civicrm' ),
			'slug' => 'street_address',
			'conditions' =>
			array(
				'type' => '',
			),
			'caption' => '',
			'config' =>
			array(
				'custom_class' => '',
				'placeholder' => '',
				'default' => '',
				'mask' => '',
				'type_override' => 'text',
			),
		),
		'supplemental_address' =>
		array(
			'ID' => 'supplemental_address',
			'type' => 'text',
			'label' => __( 'Supplemental Address', 'caldera-forms-civicrm' ),
			'slug' => 'supplemental_address',
			'conditions' =>
			array(
				'type' => '',
			),
			'caption' => '',
			'config' =>
			array(
				'custom_class' => '',
				'placeholder' => '',
				'default' => '',
				'mask' => '',
				'type_override' => 'text',
			),
		),
		'city' =>
		array(
			'ID' => 'city',
			'type' => 'text',
			'label' => __( 'City', 'caldera-forms-civicrm' ),
			'slug' => 'city',
			'conditions' =>
			array(
				'type' => '',
			),
			'required' => 1,
			'caption' => '',
			'config' =>
			array(
				'custom_class' => '',
				'placeholder' => '',
				'default' => '',
				'mask' => '',
				'type_override' => 'text',
			),
		),
		'country' =>
		array(
			'ID' => 'country',
			'type' => 'civicrm_country',
			'label' => __( 'Country', 'caldera-forms-civicrm' ),
			'slug' => 'country',
			'conditions' =>
			array(
				'type' => '',
			),
			'required' => 1,
			'caption' => '',
			'config' =>
			array(
				'custom_class' => '',
				'placeholder' => __( 'Select a Country', 'caldera-forms-civicrm' ),
				'default' => '',
			),
		),
		'state' =>
		array(
			'ID' => 'state',
			'type' => 'civicrm_state',
			'label' => __( 'State', 'caldera-forms-civicrm' ),
			'slug' => 'state',
			'conditions' =>
			array(
				'type' => '',
			),
			'caption' => '',
			'config' =>
			array(
				'custom_class' => '',
				'placeholder' => __( 'Select a State/Province', 'caldera-forms-civicrm' ),
				'default' => '',
			),
		),
		'submit' =>
		array(
			'ID' => 'submit',
			'type' => 'button',
			'label' => __( 'Submit', 'caldera-forms-civicrm' ),
			'slug' => 'submit',
			'conditions' =>
			array(
				'type' => '',
			),
			'caption' => '',
			'config' =>
			array(
				'custom_class' => '',
				'type' => 'submit',
				'class' => 'btn btn-default',
				'target' => '',
			),
		),
	),
	'page_names' =>
	array(
		0 => 'Page 1',
	),
	'mailer' =>
	array(
		'on_insert' => 1,
		'sender_name' => __( 'CiviCRM Caldera Forms', 'caldera-forms-civicrm' ),
		'sender_email' => get_option( 'admin_email' ),
		'reply_to' => '',
		'email_type' => 'html',
		'recipients' => '',
		'bcc_to' => '',
		'email_subject' => __( 'CiviCRM Caldera Forms Submission', 'caldera-forms-civicrm' ),
		'email_message' => '{summary}',
	),
	'processors' =>
	array(
		'fp_76099928' =>
		array(
			'ID' => 'fp_76099928',
			'runtimes' =>
			array(
				'insert' => 1,
			),
			'type' => 'civicrm_contact',
			'config' =>
			array(
				'address_enabled' => 1,
				'email_enabled' => 1,
				'contact_link' => 1,
				'civicrm_contact' =>
				array(
					'contact_type' => 'Organization',
					'contact_sub_type' => '',
					'dedupe_rule' => '',
					'do_not_email' => '',
					'do_not_phone' => '',
					'do_not_mail' => '',
					'do_not_sms' => '',
					'do_not_trade' => '',
					'is_opt_out' => '',
					'legal_identifier' => '',
					'nick_name' => '',
					'legal_name' => '',
					'preferred_communication_method' => '',
					'preferred_language' => '',
					'preferred_mail_format' => '',
					'source' => '',
					'first_name' => '',
					'middle_name' => '',
					'last_name' => '',
					'prefix_id' => '',
					'suffix_id' => '',
					'formal_title' => '',
					'communication_style_id' => '',
					'job_title' => '',
					'gender_id' => '',
					'birth_date' => '',
					'household_name' => '',
					'organization_name' => '%organisation_name%',
					'sic_code' => '',
					'current_employer' => '',
					'email' => '%email%',
					'custom_1' => '',
					'custom_2' => '',
					'custom_3' => '',
				),
				'civicrm_address' =>
				array(
					'location_type_id' => 2,
					'is_primary' => '',
					'is_billing' => '',
					'street_address' => '%street_address%',
					'supplemental_address_1' => '%supplemental_address%',
					'supplemental_address_2' => '',
					'city' => '%city%',
					'state_province_id' => '%state%',
					'postal_code' => '',
					'country_id' => '%country%',
				),
				'civicrm_phone' =>
				array(
					'location_type_id' => '',
					'is_primary' => '',
					'is_billing' => '',
					'phone' => '',
					'phone_numeric' => '',
					'phone_type_id' => '',
				),
				'civicrm_note' =>
				array(
					'note' => '',
					'subject' => '',
				),
				'civicrm_email' =>
				array(
					'location_type_id' => 2,
					'email' => '%email%',
					'is_primary' => '',
					'is_billing' => '',
					'on_hold' => '',
					'is_bulkmail' => '',
				),
				'civicrm_website' =>
				array(
					'website_type_id' => '',
					'url' => '',
				),
				'civicrm_group' =>
				array(
					'contact_group' => '',
				),
			),
			'conditions' =>
			array(
				'type' => '',
			),
		),
	),
	'conditional_groups' =>
	array(
		'_open_condition' => '',
	),
	'settings' =>
	array(
		'responsive' =>
		array(
			'break_point' => 'sm',
		),
	),
);
