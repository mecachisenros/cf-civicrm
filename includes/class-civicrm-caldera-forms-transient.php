<?php

/**
 * CiviCRM Caldera Forms Transient Class
 *
 * @since 0.4.4
 */
class CiviCRM_Caldera_Forms_Transient {

	/**
     * Plugin reference.
     *
     * @since 0.4.4
     * @access public
     * @var object $plugin The plugin instance
     */
    public $plugin;

    /**
     * Transient Id.
     * 
     * @since 0.4.4
     * @access protected
     * @var string $transient_id The transient id
     */
    protected static $transient_id;

    /**
     * Initialises this object.
     *
     * @since 0.4.4
     */
    public function __construct( $plugin ) {
		$this->plugin = $plugin;
    }

    /**
     * Cretate unique transient id.
     * 
     * @since 0.4.4
     * @access public
     * @return string $transient_id The transient id
     */
    public function unique_id() {
        // $transient_id = uniqid('cfc_');
        if ( ! isset( self::$transient_id ) ) self::$transient_id = uniqid( 'cfc_' );
        return self::$transient_id;
    }

    /**
     * Set transient.
     * 
     * @since 0.4.4
     * @access public
     * @param string $transient_id The transient id
     * @param object $values The values to store
     * @param int $expiration Time until expiration in seconds from now
     * @return bool True if values were stored, false otherwise
     */
    public function save( $transient_id, $data, $expiration = HOUR_IN_SECONDS ) {
        return set_transient( $transient_id, (object)$data, $expiration );
    }

    /**
     * Get transient.
     * 
     * @since 0.4.4
     * @access public
     * @return object|bool $transient The values or false if none was found
     */
    public function get( $id = null ) {
        if ( isset( $id ) ) return get_transient( self::$transient_id );
        // if ( ! isset( self::$transient_id ) ) $this->unique_id('cfc_');
        return get_transient( self::$transient_id );
    }

    /**
     * Delete transient.
     * 
     * @since 0.4.4
     * @access public
     * @return bool True if deletion was comleted, false otherwise
     */
    public function delete() {
        return delete_transient( self::$transient_id );
    }
}