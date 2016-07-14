<?php

if ( !civi_wp()->initialize() ) return;

include CF_CIVICRM_INTEGRATION_PATH . 'includes/CiviCRM_Caldera_Forms.php';

/*
* Add processors
*
* @uses "caldera_forms_get_form_processors" filter
*
* @return array Processors
*/

function cf_civicrm_register_processor( $processors ){

    $processors['civicrm_contact'] = array(
        "name"              =>  __('CiviCRM Contact'),
        "description"       =>  __('Create CiviCRM contact'),
        "author"            =>  'Andrei Mondoc',
        "pre-processor"     =>  'cf_contact_civicrm_pre_processor',
        "processor"         =>  'cf_contact_civicrm_processor',
        "template"          =>  CF_CIVICRM_INTEGRATION_PATH . "includes/contact_config.php",
    );

    $processors['civicrm_group'] = array(
        "name"              =>  __('CiviCRM Group'),
        "description"       =>  __('Add CiviCRM contact to group'),
        "author"            =>  'Andrei Mondoc',
        //"pre-processor"       =>  'cf_group_civicrm_pre_processor',
        "processor"         =>  'cf_group_civicrm_processor',
        "template"          =>  CF_CIVICRM_INTEGRATION_PATH . "includes/group_config.php",
    );

    $processors['civicrm_activity'] = array(
        "name"              =>  __('CiviCRM Activity'),
        "description"       =>  __('Add CiviCRM activity to contact'),
        "author"            =>  'Andrei Mondoc',
        //"pre-processor"       =>  'cf_activity_civicrm_pre_processor',
        "processor"         =>  'cf_activity_civicrm_processor',
        "template"          =>  CF_CIVICRM_INTEGRATION_PATH . "includes/activity_config.php",
    );

    $processors['civicrm_relationship'] = array(
        "name"              => __('CiviCRM Relationship'),
        "description"       =>  __('Add CiviCRM relationship to contacts'),
        "author"            =>  'Andrei Mondoc',
        //"pre-processor"       =>  'cf_relationship_civicrm_pre_processor',
        "processor"         =>  'cf_relationship_civicrm_processor',
        "template"          =>  CF_CIVICRM_INTEGRATION_PATH . "includes/relationship_config.php",
    );

    $processors['civicrm_entity_tag'] = array(
        "name"              => __('CiviCRM Tag'),
        "description"       =>  __('Add CiviCRM tags to contacts'),
        "author"            =>  'Andrei Mondoc',
        //"pre-processor"       =>  'cf_entity_tag_civicrm_pre_processor',
        "processor"         =>  'cf_entity_tag_civicrm_processor',
        "template"          =>  CF_CIVICRM_INTEGRATION_PATH . "includes/entity_tag_config.php",
    );
    
    $processors['civicrm_address'] = array(
        "name"              => __('CiviCRM Address'),
        "description"       =>  __('Add CiviCRM address to contacts'),
        "author"            =>  'Andrei Mondoc',
        //"pre-processor"       =>  'cf_address_civicrm_pre_processor',
        "processor"         =>  'cf_address_civicrm_processor',
        "template"          =>  CF_CIVICRM_INTEGRATION_PATH . "includes/address_config.php",
    );

    return $processors;
}

/*
* CiviCRM Contact processor
*
* @config array Processor configuration
*
* @form array Form configuration
*/

function cf_contact_civicrm_processor( $config, $form ){

    global $transdata;

    // Get form values for each processor field
    // $value is the field id
    $form_values = array();
    foreach ( $config as $key => $field_id ) { 
        $form_values[$key] = Caldera_Forms::get_field_data( $field_id, $form );
    }

    // Set Contact type and sub-type from prcessor config
    $form_values['contact_type'] = $config['contact_type'];
    $form_values['contact_sub_type'] = $config['contact_sub_type'];

    // FIXME Add First and Last name for dedupe check
    // Preapre array for contact dedupe
    $contact = array();
    $contact['email'] = $form_values['email'];

    // FIXME Make dupe rules configurable from UI
    // Dupes params
    $dedupeParams = CRM_Dedupe_Finder::formatParams( $contact, 'Individual' );
    $dedupeParams['check_permission'] = FALSE;

    // Check dupes
    $ids = CRM_Dedupe_Finder::dupesByParams( $dedupeParams, 'Individual', 'Unsupervised' );

    // Pass contact id if found
    $form_values['contact_id'] = $ids ? $ids[0] : 0;
    
    // Unset 'group', for some reason Civi's Api errors if present
    // unset( $form_values['group'] );
    $create_contact = civicrm_api3( 'Contact', 'create', $form_values );

    // Set retruned contact_id to $transdata for later use
    // $transdata['civicrm']['contact_id'] = $create_contact['id'];
    
    // Store $cid
    CiviCRM_Caldera_Forms::set_civi_transdata( $config['contact_link'], $create_contact['id'] );
    $transdata['civicrm'] = CiviCRM_Caldera_Forms::get_civi_transdata();

}

/*
*   CiviCRM Contact pre-processor
*/

function cf_contact_civicrm_pre_processor( $config, $form ){
    
}

/*
* CiviCRM Group processor
*
* @config array Processor configuration
*
* @form array Form configuration
*/

function cf_group_civicrm_processor( $config, $form ){

    global $transdata;

    // Add Contact to group
    $result = civicrm_api3( 'GroupContact', 'create', array(
        'sequential' => 1,
        'group_id' => $config['contact_group'], // Group ID from processor config
        'contact_id' => $transdata['civicrm']['contact_id_'.$config['contact_link']], // Contact ID set in Contact Processor
    ));
}

/*
* CiviCRM Group fields callback function
*
* @used by "Caldera_Forms_Processor_UI" class to build processor fields
*
* see https://gist.github.com/Shelob9/ee2210ad15f66aee40acdc8fd23f3348
*
* @returns array Fields configuration
*/
/*
function cf_civicrm_group_fields(){

    $groupsResult = civicrm_api3( 'Group', 'get', array(
        'sequential' => 1,
        'cache_date' => null,
        'is_active' => 1,
        'options' => array('limit' => 0),
    ));

    $groups = array();
    foreach ( $groupsResult['values'] as $key => $value ) {
        $group['id'] = $value['name'];
        $group['label'] = $value['title'];
        $groups[] = $group; 
    }
    return $groups;
}
*/

/*
* CiviCRM activity processor
*
* @config array Processor configuration
*
* @form array Form configuration
*/

function cf_activity_civicrm_processor( $config, $form ){

    global $transdata;

    // Get form values for each processor field
    // $value is the field id
    $form_values = array();
    foreach ( $config as $key => $field_id ) {
        $form_values[$key] = Caldera_Forms::get_field_data( $field_id, $form );
    }

    $form_values['source_contact_id'] = $transdata['civicrm']['contact_id_'.$config['contact_link']]; // Contact ID set in Contact Processor
    $form_values['activity_type_id'] = $config['activity_type_id']; // Activity Type ID
    $form_values['status_id'] = $config['status_id']; // Activity Status ID
    $form_values['campaign_id'] = $config['campaign_id']; // Campaign ID

    // FIXME
    // Concatenete DATE + TIME
    // $form_values['activity_date_time'] = $form_values['activity_date_time'];

    $create_activity = civicrm_api3( 'Activity', 'create', $form_values );
}

/*
* CiviCRM relationship processor
*
* @config array Processor configuration
*
* @form array Form configuration
*/

function cf_relationship_civicrm_processor( $config, $form ){

    global $transdata;

    $relationship = civicrm_api3('Relationship', 'get', array(
        'sequential' => 1,
        'contact_id_a' => $transdata['civicrm']['contact_id_'.$config['contact_a']],
        'contact_id_b' => $transdata['civicrm']['contact_id_'.$config['contact_b']],
        'relationship_type_id' => $config['relationship_type'],
    ));

    if( $relationship['count'] ){
        return;
    } else {

        $create_relationship = civicrm_api3('Relationship', 'create', array(
            'sequential' => 1,
            'contact_id_a' => $transdata['civicrm']['contact_id_'.$config['contact_a']],
            'contact_id_b' => $transdata['civicrm']['contact_id_'.$config['contact_b']],
            'relationship_type_id' => $config['relationship_type'],
        ));
    }

}

/*
* CiviCRM entity tag processor
*
* @config array Processor configuration
*
* @form array Form configuration
*/

function cf_entity_tag_civicrm_processor( $config, $form ){

	global $transdata;

	foreach ($config as $key=>$value) {
		if( stristr($key, 'entity_tag') != false ){
			$tag = civicrm_api3('Tag', 'getsingle', array(
  				'sequential' => 1,
  				'id' => $value,
  				'api.EntityTag.create' => array(
  					'entity_id' => $transdata['civicrm']['contact_id_'.$config['contact_link']], 
  					'entity_table' => "civicrm_contact",
  					'tag_id' => '$value.id',
  					),
			));
		}
	}
}

/*
* CiviCRM Address processor
*
* @config array Processor configuration
*
* @form array Form configuration
*/

function cf_address_civicrm_processor( $config, $form ){

    global $transdata;

    if ( !empty( $transdata['civicrm']['contact_id_'.$config['contact_link']] ) ){

        $address = civicrm_api3('Address', 'getsingle', array(
            'sequential' => 1,
            'contact_id' => $transdata['civicrm']['contact_id_'.$config['contact_link']],
            'location_type_id' => $config['location_type_id'],
        ));

        // Get form values for each processor field
        // $value is the field id
        $form_values = array();
        foreach ( $config as $key => $field_id ) {
            $form_values[$key] = Caldera_Forms::get_field_data( $field_id, $form );
        }

        $form_values['contact_id'] = $transdata['civicrm']['contact_id_'.$config['contact_link']]; // Contact ID set in Contact Processor
        //$form_values['location_type_id'] = $config['location_type_id']; // Activity Type ID
        $form_values['id'] = $address['id']; // Activity Status ID

        // FIXME
        // Concatenete DATE + TIME
        // $form_values['activity_date_time'] = $form_values['activity_date_time'];

        $create_address = civicrm_api3( 'Address', 'create', $form_values );
    }
}

function get_civi_contact( $cid ){

    $fields = civicrm_api3( 'Contact', 'getsingle', array(
        'sequential' => 1,
        'id' => $cid,
     ));

    // Custom fields
    $c_fields = CiviCRM_Caldera_Forms::get_contact_custom_fields();

    $c_fields_string = "";
    foreach ($c_fields as $key => $value) {
    	$c_fields_string .= $key.','; 
    }

    $custom_fields = civicrm_api3( 'Contact', 'getsingle', array(
        'sequential' => 1,
        'id' => $cid,
        'return' => $c_fields_string,
     ));

    return array_merge( $fields, $custom_fields );
}

/*
* Hook when form is loaded and before rendering
*
* Validates checksum and fills in the form with Contact data 
*
* @uses "caldera_forms_render_get_form" filter
*
* @return array Form
*/

add_filter( 'caldera_forms_render_get_form', 'cf_pre_render_civicrm_form' );
function cf_pre_render_civicrm_form( $form ){
    
    // Get contact_id if user is logged in
    if( is_user_logged_in() ){
        $current_user = wp_get_current_user();
        $current_user = CiviCRM_Caldera_Forms::get_wp_civi_contact( $current_user->ID );        

        $civi_contact = get_civi_contact( $current_user );
    } else {
        $civi_contact = 0;
    }

    // FIXME 
    // Just for testing, remove later
    if( isset( $_GET['cid'] ) ){
        $cid = $_GET['cid'];
        $civi_contact = get_civi_contact( $cid );
    }

    // Get request cid(contact_id) and cs(checksum)
    if( isset($_GET['cid']) && isset($_GET['cs']) ){

        $cid = $_GET['cid'];
        $cs = $_GET['cs'];

        // Check for valid checksum
        $valid_user = CRM_Contact_BAO_Contact_Utils::validChecksum( $cid, $cs );

        if( $valid_user ){
            $civi_contact = get_civi_contact( $cid );
        }

        $contactID = $civi_contact;
        
        // FIXME 
        // Add permission check
        $permissions = CRM_Core_Permission::getPermission();
    }

    // Get CiviCRM contact processor config
    $civicrm_contact_pr = Caldera_Forms::get_processor_by_type( 'civicrm_contact', $form );
    if( $civicrm_contact_pr ){
        
        /*
        foreach ($civicrm_contact_pr as $key => $value) {
            if( !is_int( $key ) ){
                unset( $civicrm_contact_pr[ $key ] );
            }
        }
        */

        // Filter empty values
        $civicrm_contact_pr = array_filter( $civicrm_contact_pr[0]['config'] );
        
        // Unset fixed config values
        unset( $civicrm_contact_pr['contact_type'], $civicrm_contact_pr['contact_sub_type'], $civicrm_contact_pr['contact_link'] );        
 		
		if( isset( $civicrm_contact_pr['auto_pop'] ) && $civicrm_contact_pr['auto_pop'] == 1 ){
            unset( $civicrm_contact_pr['auto_pop'] );
            
	        // Map CiviCRM contact data to form defaults
			if( $civi_contact ){
    			foreach ( $civicrm_contact_pr as $field => $value ) {
					$form['fields'][$value]['config']['default'] = $civi_contact[$field];
    			}
			}
 		}
		/*
        $pr = array();
        foreach ($form['processors'] as $pr_id => $value) {
            $pr[$value['ID']]['type'] =  $value['type'];
            $pr[$value['ID']]['config'] = $value['config'];
        }

        $is_relationship = Caldera_Forms::get_processor_by_type( 'civicrm_relationship', $form );
        if( $is_relationship ){
            //$is_relationship = array_filter( $is_relationship );
            $is_relationship = array_filter( $is_relationship[0]['config'] );
            $relationship = civicrm_api3('Relationship', 'get', array(
                'sequential' => 1,
                'contact_id_a' => $civi_contact['contact_id'],
                // 'contact_id_b' => $transdata['civicrm']['contact_id_'.$config['contact_b']],
                'relationship_type_id' => $is_relationship['relationship_type'],
            ));

            foreach ($pr as $pr_ID => $value) {
                if( $value['type'] == 'civicrm_relationship'){

                }
            }
        }
        */
    }

    

    return $form;
}

/*
* Hook, adds CiviCRM fields options to CF Autopopulate field type 
*
* @uses "caldera_forms_autopopulate_types" action
*/

add_action( 'caldera_forms_autopopulate_types', 'cf_civicrm_autopoulate_options' );
function cf_civicrm_autopoulate_options(){
    // Individual Prefix
    echo "<option value=\"contact_prefix_id\"{{#is auto_type value=\"contact_prefix_id\"}} selected=\"selected\"{{/is}}>" . "CiviCRM Individual Prefix" . "</option>";
    // Individual Suffix
    echo "<option value=\"suffix_id\"{{#is auto_type value=\"suffix_id\"}} selected=\"selected\"{{/is}}>" . "CiviCRM - Individual Suffix" . "</option>";
    // Individual Gender
    echo "<option value=\"contact_gender\"{{#is auto_type value=\"contact_gender\"}} selected=\"selected\"{{/is}}>" . "CiviCRM Individual Gender" . "</option>";
    // Communication Style
    echo "<option value=\"communication_style_id\"{{#is auto_type value=\"communication_style_id\"}} selected=\"selected\"{{/is}}>" . "CiviCRM - Communication Style" . "</option>";
    // Do not Email
    echo "<option value=\"do_not_email\"{{#is auto_type value=\"do_not_email\"}} selected=\"selected\"{{/is}}>" . "CiviCRM - Do Not Email" . "</option>";
    // Do not Phone
    echo "<option value=\"do_not_phone\"{{#is auto_type value=\"do_not_phone\"}} selected=\"selected\"{{/is}}>" . "CiviCRM - Do Not Phone" . "</option>";
    // Do not Mail
    echo "<option value=\"do_not_mail\"{{#is auto_type value=\"do_not_mail\"}} selected=\"selected\"{{/is}}>" . "CiviCRM - Do Not Mail" . "</option>";
    // Do not SMS
    echo "<option value=\"do_not_sms\"{{#is auto_type value=\"do_not_sms\"}} selected=\"selected\"{{/is}}>" . "CiviCRM - Do Not SMS" . "</option>";
    // Do not Trade
    echo "<option value=\"do_not_trade\"{{#is auto_type value=\"do_not_trade\"}} selected=\"selected\"{{/is}}>" . "CiviCRM - Do Not Trade" . "</option>";
    // Is Opt Out
    echo "<option value=\"is_opt_out\"{{#is auto_type value=\"is_opt_out\"}} selected=\"selected\"{{/is}}>" . "CiviCRM - No Bulk Emails (User Opt Out)" . "</option>";
    // Country
    echo "<option value=\"country_id\"{{#is auto_type value=\"country_id\"}} selected=\"selected\"{{/is}}>" . "CiviCRM - Country" . "</option>";
    // State/Provine
    echo "<option value=\"state_province_id\"{{#is auto_type value=\"state_province_id\"}} selected=\"selected\"{{/is}}>" . "CiviCRM - State/Province" . "</option>";
}

/*
* Hook, populates CiviCRM fields values for each CiviCRM CF Autopopulate field type 
*
* @uses "caldera_forms_render_get_field" filter
*
* @field array the field to populate
*
* @form array Form
*
* @returns array Field
*/

add_filter( 'caldera_forms_render_get_field', 'cf_civicrm_autopoulate_values', 20, 2 );
function cf_civicrm_autopoulate_values( $field, $form ){

    if ( !empty( $field['config']['auto'] ) ){
        switch ( $field['config']['auto_type'] ){
            
            // Prefix
            case 'contact_prefix_id':
                $prefix_id = civicrm_api3('Contact', 'getoptions', array(
                    'sequential' => 1,
                    'field' => "prefix_id",
                ));
                foreach ($prefix_id['values'] as $index) {
                    //foreach ($index as $key => $value) {
                        $field['config']['option'][$index['key']] = array(
                            'value' => $index['key'],
                            'label' => $index['value']
                        );
                    //}
                }
                break;
                
            // Suffix
            case 'suffix_id':
                $suffix_id = civicrm_api3('Contact', 'getoptions', array(
                    'sequential' => 1,
                    'field' => "suffix_id",
                ));
                foreach ($suffix_id['values'] as $index) {
                        $field['config']['option'][$index['key']] = array(
                            'value' => $index['key'],
                            'label' => $index['value']
                        );
                }
                break;
                
            // Gender
            case 'contact_gender':
                $prefix_id = civicrm_api3('Contact', 'getoptions', array(
                    'sequential' => 1,
                    'field' => "gender_id",
                ));
                foreach ($prefix_id['values'] as $index) {
                    //foreach ($index as $key => $value) {
                        $field['config']['option'][$index['key']] = array(
                            'value' => $index['key'],
                            'label' => $index['value']
                        );
                    //}
                }
                break;
                
			// Communication Style
            case 'communication_style_id':
                $communication_style_id = civicrm_api3('Contact', 'getoptions', array(
                    'sequential' => 1,
                    'field' => "communication_style_id",
                ));
                foreach ($communication_style_id['values'] as $index) {
                        $field['config']['option'][$index['key']] = array(
                            'value' => $index['key'],
                            'label' => $index['value']
                        );
                }
                break;

            // Do Not Email
            case 'do_not_email':
                $do_not_email = civicrm_api3('Contact', 'getoptions', array(
                    'sequential' => 1,
                    'field' => "do_not_email",
                ));
                foreach ($do_not_email['values'] as $index) {
                        $field['config']['option'][$index['key']] = array(
                            'value' => $index['key'],
                            'label' => $index['value']
                        );
                }
                break;

            // Do Not Phone
            case 'do_not_phone':
                $do_not_phone = civicrm_api3('Contact', 'getoptions', array(
                    'sequential' => 1,
                    'field' => "do_not_phone",
                ));
                foreach ($do_not_phone['values'] as $index) {
                        $field['config']['option'][$index['key']] = array(
                            'value' => $index['key'],
                            'label' => $index['value']
                        );
                }
                break;

            // Do Not Mail
            case 'do_not_mail':
                $do_not_mail = civicrm_api3('Contact', 'getoptions', array(
                    'sequential' => 1,
                    'field' => "do_not_mail",
                ));
                foreach ($do_not_mail['values'] as $index) {
                        $field['config']['option'][$index['key']] = array(
                            'value' => $index['key'],
                            'label' => $index['value']
                        );
                }
                break;

            // Do Not SMS
            case 'do_not_sms':
                $do_not_sms = civicrm_api3('Contact', 'getoptions', array(
                    'sequential' => 1,
                    'field' => "do_not_sms",
                ));
                foreach ($do_not_sms['values'] as $index) {
                        $field['config']['option'][$index['key']] = array(
                            'value' => $index['key'],
                            'label' => $index['value']
                        );
                }
                break;

            // Do Not Trade
            case 'do_not_trade':
                $do_not_trade = civicrm_api3('Contact', 'getoptions', array(
                    'sequential' => 1,
                    'field' => "do_not_trade",
                ));
                foreach ($do_not_trade['values'] as $index) {
                        $field['config']['option'][$index['key']] = array(
                            'value' => $index['key'],
                            'label' => $index['value']
                        );
                }
                break;

            // Is Opt Out
            case 'is_opt_out':
                $is_opt_out = civicrm_api3('Contact', 'getoptions', array(
                    'sequential' => 1,
                    'field' => "is_opt_out",
                ));
                foreach ($is_opt_out['values'] as $index) {
                        $field['config']['option'][$index['key']] = array(
                            'value' => $index['key'],
                            'label' => $index['value']
                        );
                }
                break;

            // Country
            case 'country_id':
                $country_id = civicrm_api3('Country', 'get', array(
                    'sequential' => 1,
                    'options' => array('limit' => 0),
                ));
                foreach ($country_id['values'] as $key=>$value) {
                        $field['config']['option'][$value['id']] = array(
                            'value' => $value['id'],
                            'label' => $value['name']
                        );
                }
                break;

            // State/Province
            case 'state_province_id':
                $state_province_id = CiviCRM_Caldera_Forms::get_state_province();
                foreach ($state_province_id as $key=>$value) {
                        $field['config']['option'][$key] = array(
                            'value' => $key,
                            'label' => $value
                        );
                }
                break;
        }
    }
    return $field;
}

/*
* Hook, adds custom fields to Caldera UI 
*
* @uses "caldera_forms_get_field_types" filter
*
* @fieldtypes array Fields configuration
*
* @returns array $fieldtypes
*/

add_filter('caldera_forms_get_field_types', 'cf_civicrm_fields');
function cf_civicrm_fields( $fieldtypes ){
    $fieldtypes['civicrm_country'] = array(
        "field"         =>  "CiviCRM Country",
        "file"          =>  CF_CIVICRM_INTEGRATION_PATH . "fields/civicrm_country/field.php",
        "category"      =>  "CiviCRM",
        "description"   =>  'CiviCRM Country dropdown',
        "setup"         =>  array(
            "template"  =>  CF_CIVICRM_INTEGRATION_PATH . "fields/civicrm_country/config.php",
            "preview"   =>  CF_CIVICRM_INTEGRATION_PATH . "fields/civicrm_country/preview.php",
            "default"   =>  array('placeholder' => 'Select a Country'),
            "not_supported" =>  array(
                'entry_list',
            )
        )
    );

    $fieldtypes['civicrm_state'] = array(
        "field"         =>  "CiviCRM State/Province",
        "file"          =>  CF_CIVICRM_INTEGRATION_PATH . "fields/civicrm_state/field.php",
        "category"      =>  "CiviCRM",
        "description"   => 'CiviCRM State/Province dropdown',
        "setup"         =>  array(
            "template"  =>  CF_CIVICRM_INTEGRATION_PATH . "fields/civicrm_state/config.php",
            "preview"   =>  CF_CIVICRM_INTEGRATION_PATH . "fields/civicrm_state/preview.php",
            "default"   =>  array('placeholder' => 'Select a State/Province'),
            "not_supported" =>  array(
                'entry_list',
            )
        )
    );

    return $fieldtypes;
}

