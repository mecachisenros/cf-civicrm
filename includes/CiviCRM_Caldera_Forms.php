<?php

/**
* 
*/

class CiviCRM_Caldera_Forms {

    // public static $fields_to_unset = array('id', 'contact_type', 'contact_sub_type', 'legal_identifier', 'external_identifier', 'sort_name', 'display_name', 'legal_name', 'image_URL', 'hash', 'api_key', 'email_greeting_id', 'email_greeting_custom', 'email_greeting_display', 'postal_greeting_id', 'postal_greeting_custom', 'postal_greeting_display', 'addressee_id', 'addressee_custom', 'addressee_display', 'is_deceased', 'deceased_date', 'primary_contact_id', 'sic_code', 'user_unique_id', 'employer_id', 'is_deleted', 'created_date', 'modified_date');
    
    public static $contact_fields = array( 'prefix_id', 'first_name', 'last_name', 'middle_name', 'suffix_id', 'is_opt_out', 'nick_name', 'source', 'formal_title', 'job_title', 'gender_id', 'birth_date', 'email', 'current_employer');

    public static $activity_fields = array( 'activity_type_id', 'phone_id', 'phone_number', 'status_id', 'priority_id', 'parent_id', 'is_test', 'medium_id', 'is_auto', 'is_current_revision', 'result', 'is_deleted', 'campaign_id', 'engagement_level', 'weight', 'id', 'original_id');

    public static $civi_transdata = array();

    public static function set_civi_transdata( $contact_link, $cid ){

        self::$civi_transdata['contact_id_' . $contact_link] = $cid;
        
    }

    public static function get_civi_transdata(){
        return self::$civi_transdata;
    }


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
        $output = ob_end_flush();
        return $contact_link;
    }

	
    /*
    * Get CiviCRM contact's custom fields
    *
    * @returns array // "custom_x" => "Label of custom_x" 
    */

	public static function get_contact_custom_fields(){

		$custom_group = civicrm_api3( 'CustomGroup', 'get', array(
		        'sequential' => 1,
		        'extends' => "contact",
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
	
}
