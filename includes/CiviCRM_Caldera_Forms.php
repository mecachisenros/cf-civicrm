<?php

/**
* Helper Class
*/

class CiviCRM_Caldera_Forms {

    public static $contact_fields = array( 'prefix_id', 'first_name', 'last_name', 'middle_name', 'suffix_id', 'is_opt_out', 'nick_name', 'source', 'formal_title', 'job_title', 'gender_id', 'birth_date', 'email', 'current_employer', 'do_not_phone', 'do_not_email', 'do_not_mail', 'do_not_sms', 'do_not_trade', 'legal_identifier', 'legal_name', 'preferred_communication_method', 'preferred_language', 'preferred_mail_format', 'communication_style_id', 'household_name', 'organization_name', 'sic_code' );

    public static $activity_fields = array( 'activity_type_id', 'phone_id', 'phone_number', 'status_id', 'priority_id', 'parent_id', 'is_test', 'medium_id', 'is_auto', 'is_current_revision', 'result', 'is_deleted', 'campaign_id', 'engagement_level', 'weight', 'id', 'original_id', 'relationship_id');

	/*
    * @array Holds contact ids for linking processors
    */

    public static $civi_transdata = array();

    /*
    * Sets the contact_id/contact_id mapping
    *
    * @contact_link Integer Contact link from processot $config
    *
    * @cid Integet Contact ID
    */

    public static function set_civi_transdata( $contact_link, $cid ){

        self::$civi_transdata['contact_id_' . $contact_link] = $cid;

    }

    /*
    * @array Returns the contact_link/contact_id mapping
    */

    public static function get_civi_transdata(){
        return self::$civi_transdata;
    }

    /*
    * Outputs HTML
    */

    public static function contact_link_field(){
        ob_start();
        ?>
                <select class="block-input field-config" name="{{_name}}[contact_link]">
                    <option value="1" {{#is contact_link value=1}}selected="selected"{{/is}}>Contact 1</option>
                    <option value="2" {{#is contact_link value=2}}selected="selected"{{/is}}>Contact 2</option>
                    <option value="3" {{#is contact_link value=3}}selected="selected"{{/is}}>Contact 3</option>
                    <option value="4" {{#is contact_link value=4}}selected="selected"{{/is}}>Contact 4</option>
                    <option value="5" {{#is contact_link value=5}}selected="selected"{{/is}}>Contact 5</option>
                    <option value="6" {{#is contact_link value=6}}selected="selected"{{/is}}>Contact 6</option>
                    <option value="7" {{#is contact_link value=7}}selected="selected"{{/is}}>Contact 7</option>
                    <option value="8" {{#is contact_link value=8}}selected="selected"{{/is}}>Contact 8</option>
                    <option value="9" {{#is contact_link value=9}}selected="selected"{{/is}}>Contact 9</option>
                    <option value="10" {{#is contact_link value=10}}selected="selected"{{/is}}>Contact 10</option>
                </select>
        <?php
        $contact_link = ob_get_contents();
        return $contact_link;
    }


    /*
    * Get CiviCRM contact's custom fields
    *
    * @returns array // "custom_x" => "Label of custom_x"
    */

	public static function get_contact_custom_fields(){

		$contact_types = civicrm_api3('ContactType', 'get', array(
            'sequential' => 1,
        ));

		// Include Contact entity by default
        $types = array('Contact');
        foreach ( $contact_types['values'] as $key => $value ) {
            $types[] = $value['name'];
        }

        $extends = array('IN' => $types );

		$custom_group = civicrm_api3( 'CustomGroup', 'get', array(
		        'sequential' => 1,
		        'extends' => apply_filters( 'civicrm_custom_fields_contact_type', $extends ),
		        'api.CustomField.get' => array(),
		    ));

		$custom_fields = array();
		foreach ( $custom_group['values'] as $key => $value ) {
		    foreach ( $value['api.CustomField.get']['values'] as $k => $v ) {
		        $custom_fields['custom_' . $v['id']] = $v['label'];
		    }
		}
		return $custom_fields;
	}

    /*
    * Get CiviCRM contact for a WP user
    *
    * @id WP User ID
    *
    * @retunrns contact_id
    */

    public static function get_wp_civi_contact($id){

        $wp_civicrm_contact = civicrm_api3( 'UFMatch', 'getsingle', array(
            'sequential' => 1,
            'uf_id' => $id,
        ));
        return $wp_civicrm_contact['contact_id'];
    }

    /*
    *
    *
    *
    */

	public static function get_all_fields(){

		$contact_fields = civicrm_api3( 'Contact', 'getfields', array(
        	'sequential' => 1,
    	));
        return $contact_fields['values'];
	}

	/*
    * @states array Get State/Province from Civicrm
    *
    * @retunrs states
    */

    public static function get_state_province(){

        $query = "SELECT name,id,country_id FROM civicrm_state_province";
        $dao = CRM_Core_DAO::executeQuery($query);
        $states = array();

        while ( $dao->fetch() ) {
            $states[$dao->id] = array('name' => $dao->name, 'country_id' => $dao->country_id );
        }

        return $states;
    }

    /*
    * @setting String, Name of the setting to be returned
    *
    * @returns setting
    */

    public static function get_civicrm_settings( $setting ){
        $settings = civicrm_api3('Setting', 'getvalue', array(
            'sequential' => 1,
            'name' => $setting,
        ));

        return $settings;
    }

    /*
    * Get Deduplicate rules
    *
    * @returns array
    */

    public static function get_dedupe_rules(){

        $dedupe_rules['Organization'] = CRM_Dedupe_BAO_RuleGroup::getByType('Organization');
        $dedupe_rules['Individual'] = CRM_Dedupe_BAO_RuleGroup::getByType('Individual');
        $dedupe_rules['Household'] = CRM_Dedupe_BAO_RuleGroup::getByType('Household');

        return $dedupe_rules;
    }

}
