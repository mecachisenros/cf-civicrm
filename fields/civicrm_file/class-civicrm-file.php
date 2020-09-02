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

		// handle advanced file 2.0 cf2_file
		add_filter( 'rest_dispatch_request', [ $this, 'handle_cf2_advanced_file_upload' ], 10, 3 );

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
			'upload_date' => date( 'YmdHis', strtotime( 'now' ) ),
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
	 * Filters the rest resquest for cf2 advanced file uploads.
	 *
	 * The adcnaved file upload (2.0) doesn't trigger the
	 * 'caldera_forms_file_upload_handler' filter hence this workaround,
	 * we filter the rest request for the file route, grab the necessary
	 * params, and hook twice in wp_handle_upload() function to create
	 * and pass on our file id.
	 *
	 * @since 1.0.5
	 * @param mixed $result The result
	 * @param WP_REST_Request $request The request
	 * @param string $route The route
	 * @return mixed $result
	 */
	public function handle_cf2_advanced_file_upload( $result, WP_REST_Request $request, $route ) {

		if ( $route != '/cf-api/v3/file' ) return $result;

		$params = $request->get_params();

		if ( empty( $params['formId'] ) || empty( $params['fieldId'] ) || empty( $params['hashes'] ) ) return $result;

		$form = Caldera_Forms::get_form( $params['formId'] );
		$field = Caldera_Forms_Field_Util::get_field( $params['fieldId'], $form );

		if ( empty( $field['config']['civicrm_file_upload'] ) ) return $result;

		if ( empty( $_FILES ) ) return $result;

		if ( empty( $_FILES['file'] ) ) return $result;

		if ( ! Caldera_Forms_Render_Nonce::verify_nonce( $params['verify'], $params['formId'] ) ) return $result;

		$file_to_move = $_FILES['file'];

		if ( ! hash_equals( md5_file( $file_to_move['tmp_name'] ), $params['hashes'] ) ) return $result;

		$args = [
			'form_id' => $params['formId'],
			'field_id' => $params['fieldId'],
		];

		$file_id = null;

		// filter file upload before wp handles it
		add_filter( 'pre_move_uploaded_file', function( $abort, $file ) use ( $file_to_move, $args, &$file_id ) {

			if ( $file !== $file_to_move ) return $abort;

			// its a civicrm file, create it
			$upload = $this->handle_civicrm_uploads( $file, $args );
			$file_id = $upload['url'];

			return true;

		}, 10, 2 );

		// pass our file id as the url once wp has handled the file
		add_filter( 'wp_handle_upload', function( $upload ) use ( &$file_id ) {

			if ( $file_id ) {
				$upload['url'] = $file_id;
			}

			return $upload;

		} );

		return $result;

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

		if( $field_slug == 'file' ) include CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_file/file_config.php';
		if( in_array( $field_slug, [ 'advanced_file', 'cf2_file' ] ) ) include CF_CIVICRM_INTEGRATION_PATH . 'fields/civicrm_file/advanced_file_config.php';

	}
}
