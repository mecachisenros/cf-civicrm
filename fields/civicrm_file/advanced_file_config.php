<div class="caldera-config-group">
  <label role="presentation"></label>
  <div class="caldera-config-field">
  	<label for="{{_id}}_civicrm_file_upload">
  		<input id="{{_id}}_civicrm_file_upload" type="checkbox" class="field-config" name="{{_name}}[civicrm_file_upload]" {{#if civicrm_file_upload}}checked="checked"{{/if}} value="1">
  		<?php _e( 'CiviCRM File Upload', 'caldera-forms-civicrm' ); ?></label>
  </div>
</div>
{{#script}}
jQuery( document ).ready( function( $ ){
	$('#{{_id}}_civicrm_file_upload').change(function(){
			if( $(this).prop('checked') ){
    		    $('#{{_id}}_attach').prop('checked', false);
				$('#{{_id}}_media_library').prop('checked', false);
    		    $('#{{_id}}_attach').hide();
    		    $('#{{_id}}_attach').parent().parent().hide();
				$('#{{_id}}_media_library').hide();
				$('#{{_id}}_media_library').parent().parent().hide();
			}else{
    		    $('#{{_id}}_attach').show();
    		    $('#{{_id}}_attach').parent().parent().show();
				$('#{{_id}}_media_library').parent().parent().show();
				$('#{{_id}}_media_library').show();
			}
		});
	$('#{{_id}}_civicrm_file_upload').trigger('change');
} );
{{/script}}
