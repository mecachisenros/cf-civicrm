<?php
/**
 * CiviCRM Caldera Forms - Contact starter template.
 *
 * @since 0.3
 */

return array(
	'name' => __( 'CiviCRM Contact Form', 'caldera-forms-civicrm' ),
	'description' => __( 'Basic CiviCRM contact form.', 'caldera-forms-civicrm' ),
	'db_support' => 1,
	'pinned' => 0,
	'hide_form' => 1,
	'check_honey' => 0,
	'success' => __( 'Form has been successfully submitted. Thank you.', 'caldera-forms-civicrm' ),
	'avatar_field' => '',
	'form_ajax' => 1,
	'custom_callback' => '',
	'layout_grid' =>
	array(
		'fields' =>
		array(
			'prefix' => '1:1',
			'first_name' => '1:2',
			'last_name' => '1:3',
			'contact_email' => '2:1',
			'submit' => '3:1',
		),
		'structure' => '2:5:5|12|12',
	),
	'fields' =>
	array(
		'prefix' =>
		array(
			'ID' => 'prefix',
			'type' => 'dropdown',
			'label' => __( 'Prefix', 'caldera-forms-civicrm' ),
			'slug' => 'prefix',
			'conditions' =>
			array(
				'type' => '',
			),
			'caption' => '',
			'config' =>
			array(
				'custom_class' => '',
				'placeholder' => '',
				'auto' => 1,
				'auto_type' => 'prefix_id',
				'taxonomy' => 'category',
				'post_type' => 'post',
				'value_field' => 'name',
				'orderby_tax' => 'name',
				'orderby_post' => 'name',
				'order' => 'ASC',
				'default' => '',
			),
		),
		'first_name' =>
		array(
			'ID' => 'first_name',
			'type' => 'text',
			'label' => __( 'First Name', 'caldera-forms-civicrm' ),
			'slug' => 'first_name',
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
		'last_name' =>
		array(
			'ID' => 'last_name',
			'type' => 'text',
			'label' => __( 'Last Name', 'caldera-forms-civicrm' ),
			'slug' => 'last_name',
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
		'contact_email' =>
		array(
			'ID' => 'contact_email',
			'type' => 'email',
			'label' => __( 'Email', 'caldera-forms-civicrm' ),
			'slug' => 'contact_email',
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
		'fp_67800236' =>
		array(
			'ID' => 'fp_67800236',
			'runtimes' =>
			array(
				'insert' => 1,
			),
			'type' => 'civicrm_contact',
			'config' =>
			array(
				'auto_pop' => 1,
				'enabled_entities' =>
				array(
					'process_email' => 1
				),
				'contact_link' => 1,
				'civicrm_contact' =>
				array(
					'contact_type' => 'Individual',
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
					'first_name' => '%first_name%',
					'middle_name' => '',
					'last_name' => '%last_name%',
					'prefix_id' => '%prefix%',
					'suffix_id' => '',
					'formal_title' => '',
					'communication_style_id' => '',
					'job_title' => '',
					'gender_id' => '',
					'birth_date' => '',
					'household_name' => '',
					'organization_name' => '',
					'sic_code' => '',
					'current_employer' => '',
					'email' => '%contact_email%',
					'custom_1' => '',
					'custom_2' => '',
					'custom_3' => '',
				),
				'civicrm_address' =>
				array(
					'location_type_id' => '',
					'is_primary' => '',
					'is_billing' => '',
					'street_address' => '',
					'supplemental_address_1' => '',
					'supplemental_address_2' => '',
					'city' => '',
					'state_province_id' => '',
					'postal_code' => '',
					'country_id' => '',
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
					'location_type_id' => 3,
					'email' => '%contact_email%',
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
	),
	'settings' =>
	array(
		'responsive' =>
		array(
			'break_point' => 'sm',
		),
	),
);
