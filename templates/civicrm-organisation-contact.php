<?php
/**
 * CiviCRM Caldera Forms - Organisation and Contact starter template.
 *
 * @since 0.3
 */

return array(
	'name' => __( 'CiviCRM Organisation and Contact', 'caldera-forms-civicrm' ),
	'description' => __( 'CiviCRM organisation form with primary contact.', 'caldera-forms-civicrm' ),
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
			'about_your_organisation' => '1:1',
			'organisation_name' => '1:1',
			'org_email' => '1:1',
			'street_address' => '1:1',
			'supplemental_address' => '1:1',
			'city' => '1:1',
			'state' => '2:1',
			'country' => '2:2',
			'organisation_contact' => '3:1',
			'prefix' => '4:1',
			'first_name' => '4:2',
			'last_name' => '4:3',
			'contact_email' => '5:1',
			'submit' => '6:1',
		),
		'structure' => '12|6:6|12|2:5:5|12|12',
	),
	'fields' =>
	array(
		'about_your_organisation' =>
		array(
			'ID' => 'about_your_organisation',
			'type' => 'html',
			'label' => __( 'About Your Organisation', 'caldera-forms-civicrm' ),
			'slug' => 'about_your_organisation',
			'conditions' =>
			array(
				'type' => '',
			),
			'caption' => '',
			'config' =>
			array(
				'custom_class' => '',
				'default' => '<h2>' . __( 'About Your Organisation', 'caldera-forms-civicrm' ) . '</h2>

<p>' . __( 'We need to know a few things about your organisation.', 'caldera-forms-civicrm' ) . '</p>',
			),
		),
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
		'org_email' =>
		array(
			'ID' => 'org_email',
			'type' => 'email',
			'label' => __( 'Organisation Email', 'caldera-forms-civicrm' ),
			'slug' => 'org_email',
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
		'organisation_contact' =>
		array(
			'ID' => 'organisation_contact',
			'type' => 'html',
			'label' => __( 'Organisation Contact', 'caldera-forms-civicrm' ),
			'slug' => 'organisation_contact',
			'conditions' =>
			array(
				'type' => '',
			),
			'caption' => '',
			'config' =>
			array(
				'custom_class' => '',
				'default' => '<hr />

<h2>' . __( 'Organisation Contact', 'caldera-forms-civicrm' ) . '</h2>

<p>' . __( 'Who is the primary contact at your organisation?', 'caldera-forms-civicrm' ) . '</p>',
			),
		),
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
			'label' => __( 'Contact Email', 'caldera-forms-civicrm' ),
			'slug' => 'contact_email',
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
		'fp_57688899' =>
		array(
			'ID' => 'fp_57688899',
			'runtimes' =>
			array(
				'insert' => 1,
			),
			'type' => 'civicrm_contact',
			'config' =>
			array(
				'auto_pop' => 1,
				'email_enabled' => 1,
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
					'location_type_id' => 2,
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
				'contact_link' => 2,
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
					'email' => '%org_email%',
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
					'email' => '%org_email%',
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
		'fp_72121786' =>
		array(
			'ID' => 'fp_72121786',
			'runtimes' =>
			array(
				'insert' => 1,
			),
			'type' => 'civicrm_relationship',
			'config' =>
			array(
				'relationship_type' => 5,
				'contact_a' => 1,
				'contact_b' => 2,
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
