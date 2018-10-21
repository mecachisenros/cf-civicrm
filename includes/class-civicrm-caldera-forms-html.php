<?php

/**
 * CiviCRM Caldera Forms HTML Class.
 *
 * @since 0.4.4
 */
class CiviCRM_Caldera_Forms_HTML {

	/**
	 * Plugin reference.
	 *
	 * @since 0.4.4
	 * @access public
	 * @var object $plugin The plugin instance
	 */
	public $plugin;

	/**
	 * Initialises this object.
	 *
	 * @since 0.4.4
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}
	
	/**
	 * Output html buffer content.
	 *
	 * @since 0.4.4
	 * 
	 * @param mixed $data Data to make available in the template.
	 * @param string $template_path The template path
	 * @param object $plugin Reference to this plugin
	 * @return string $output Contents of output buffer
	 */
	public function generate( $data, $template_path ) {
		// plugin reference
		$plugin = $this->plugin;
		
		// no template path message
		if ( ! is_readable( $template_path ) )
			return sprintf('<h2>Could not read <em>"%s"</em> file.<h2>', $template_path );

		ob_start();

		include $template_path;

		return ob_get_clean();

	}

}