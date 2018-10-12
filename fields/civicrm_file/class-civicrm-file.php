<?php

/**
 * CiviCRM Caldera Forms File Field Class.
 *
 * @since 0.4.2
 */
class CiviCRM_Caldera_Forms_Field_File {

	/**
     * Plugin reference.
     *
     * @since 0.4.4
     */
    public $plugin;

	/**
	 * CiviCRM file mapping fields.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var array $files
	 */
	public $file_fields = [];

	/**
	 * Initialises this object.
	 *
	 * @since 0.4.2
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
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
		add_filter( 'caldera_forms_file_upload_handler', [ $this, 'civicrm_upload_handler' ], 10, 3 );

		// add civicrm file upload config template for file field
		add_action( 'caldera_forms_field_settings_template', [ $this, 'civicrm_upload_config_template' ], 20, 2 );


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
		if ( in_array( $field['type'], [ 'file', 'advanced_file' ] ) && ! isset( $field['config']['civicrm_file_upload'] ) ) return $handler;

		// build fields array
		if ( empty( $this->file_fields ) ) {
			$this->file_fields[$field['ID']] = [
				'form_id' => $form['ID'],
				'files' => []
			];
		}

		return [ $this, 'handle_civicrm_uploads' ];

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

		// upload directory
		$upload_directory = CRM_Core_Config::singleton()->customFileUploadDir;

		$params = [
			'name' => $file['name'],
			'mime_type' => $file['type'],
			'tmp_name' => $file['tmp_name'],
			'uri' => CRM_Utils_File::makeFileName( str_replace( ' ', '_', $file['name'] ) )
		];

		move_uploaded_file( $file['tmp_name'], $upload_directory . $params['uri'] );

		// this triggers more than once per form
		if ( empty( $this->file_fields[$args['field_id']]['files'] ) ) {
			// create and add file to array
			$create_file = civicrm_api3( 'File', 'create', $params );
			$this->file_fields[$args['field_id']]['files'][$create_file['id']] = $file;

		} else {
			// check if file is already added
			foreach ( $this->file_fields[$args['field_id']]['files'] as $file_id => $file_parts ) {
				if ( $file_parts !==  $file ) {
					$create_file = civicrm_api3( 'File', 'create', $params );
					$this->file_fields[$args['field_id']]['files'][$create_file['id']] = $file;
				}
			}
		}

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
