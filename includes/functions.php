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

    $form_values['source_record_id'] = $transdata['civicrm']['contact_id_'.$config['contact_link']]; // Contact ID set in Contact Processor
    $form_values['activity_type_id'] = $config['activity_type_id']; // Activity Type ID
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
        $contactID = $civi_contact;
    } else {
        $civi_contact = 0;
    }

    // FIXME 
    // Just for testing, remove later
    if( isset( $_GET['cid'] ) ){
        $cid = $_GET['cid'];
        $civi_contact = get_civi_contact( $cid );
    }

/*
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
*/

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
        // FIXME 
        // Map CiviCRM contact data to form defaults
        // Custom fields are not returned by civicrm_api3
        if( $civi_contact ){
            foreach ( $civicrm_contact_pr as $field => $value ) {
                $form['fields'][$value]['config']['default'] = $civi_contact[$field];
            }
        }

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
    // Membeships
    echo "<option value=\"civicrm_memberships\"{{#is auto_type value=\"civicrm_memberships\"}} selected=\"selected\"{{/is}}>" . "CiviCRM Memberships" . "</option>";
    // Individual Prefix
    echo "<option value=\"contact_prefix_id\"{{#is auto_type value=\"contact_prefix_id\"}} selected=\"selected\"{{/is}}>" . "CiviCRM Individual Prefix" . "</option>";
    // Individual Gender
    echo "<option value=\"contact_gender\"{{#is auto_type value=\"contact_gender\"}} selected=\"selected\"{{/is}}>" . "CiviCRM Individual Gender" . "</option>";
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
            // Memberships
            case 'civicrm_memberships':
                $memberships = civicrm_api3( 'MembershipType', 'get', array(
                    'sequential' => 1,
                ));
                foreach ( $memberships['values'] as $key => $value ) {
                    $field['config']['option'][$value['id']] = array(
                        'value' => $value['id'],
                        'label' => $value['name']
                    );
                }
                break;
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
        }
    }
    return $field;
}


