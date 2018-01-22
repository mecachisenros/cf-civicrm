<?php

/**
 * CiviCRM Caldera Forms File Field Class.
 *
 * @since 0.4.2
 */
class CiviCRM_Caldera_Forms_Field_File {

	/**
	 * CiviCRM file mapping fields.
	 *
	 * @since 0.4.2
	 * @access public
	 * @var array $files
	 */
	public $files = array();

	/**
	 * Count files.
	 *
	 * @since 0.4.2
	 * @access protected
	 * @var int $files
	 */
	protected $count = 0;

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

		// add civicrm upload handler
		add_filter( 'caldera_forms_file_upload_handler', array( $this, 'civicrm_upload_handler' ), 10, 3 );

		// add civicrm file upload config template for file field
		add_action( 'caldera_forms_field_settings_template', array( $this, 'civicrm_upload_config_template' ), 20, 2 );

	}

	/**
	 * Handle upload callback function.
	 *
	 * @uses 'caldera_forms_file_upload_handler' filter
	 *
	 * @since 0.4.2
	 *
	 * @param array|string|callable $handler Callable
	 * @param array $form Form config
 	 * @param array $field Field config
	 * @return array $handler The custom upload callback
	 */
	public function civicrm_upload_handler( $handler, $form, $field ) {

		// abort if civicrm upload is not enable
		if ( in_array( $field['type'], array( 'file', 'advanced_file' ) ) && ! isset( $field['config']['civicrm_file_upload'] ) ) return $handler;

		$this->count++;
		$this->files['file_' . $this->count] = array(
			'field_id' => $field['ID'],
			'upload' => $field['config']['civicrm_file_upload'],
			'file_id' => ''
		);

		return array( $this, 'handle_civicrm_uploads' );

	}

	/**
	 * CiviCRM upload handler.
	 *
	 * @since 0.4.2
	 *
	 * @param array $file The file
	 * @param array $args
	 * @return array $upload
	 */
	public function handle_civicrm_uploads( $file, $args ) {

		// we can't use the Attachment API because it requires an entity_id
		// by the time the files are uploded the processors have not been precessed yet
		// therefore we create a File and store the reference in a transient
  		$upload_directory = CRM_Core_Config::singleton()->customFileUploadDir;

		$params = array(
			'name' => $file['name'],
			'mime_type' => $file['type'],
			'tmp_name' => $file['tmp_name'],
			'uri' => CRM_Utils_File::makeFileName( str_replace( ' ', '_', $file['name'] ) )
		);

		move_uploaded_file( $file['tmp_name'], $upload_directory . $params['uri'] );

		$create_file = civicrm_api3( 'File', 'create', $params );

		foreach ( $_FILES as $field_id => $parts ) {
			if( $file === $parts ) {
				foreach ( $this->files as $file_number => $map ) {
					if ( $this->files[$file_number]['field_id'] == $field_id  && ! empty( $this->files[$file_number]['upload'] ) ) {
						$this->files[$file_number]['file_id'] = $create_file['id'];
					}
				}
			}
 		}

		CiviCRM_Caldera_Forms_Helper::set_file_entity_ids( $this->files );

		$upload['url'] = $create_file['id'];
		$upload['type'] = $create_file['values']['mime_type'];

		return $upload;

	}

	/**
	 * Adds CiviCRM File Uplad config template.
	 *
	 * @uses 'caldera_forms_field_settings_template' action
	 *
	 * @since 0.4.2
	 *
	 * @param array $config The field config
	 * @param string $field_slug The field slug
	 */
	public function civicrm_upload_config_template( $config, $field_slug ) {

		if( $config['field'] == 'File' ) include CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_file/file_config.php';
		if( $config['field'] == 'Advanced File Uploader' ) include CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_file/advanced_file_config.php';

	}
}
