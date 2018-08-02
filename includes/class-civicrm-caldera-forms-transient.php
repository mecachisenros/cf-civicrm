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
     * Transient name prefix.
     *
     * @since 0.4.4
     * @access protected
     * @var string $preifx Transient name prefix
     */
    protected $prefix = 'cfc_';

    /**
     * Transient Id.
     * 
     * @since 0.4.4
     * @access protected
     * @var string $transient_id The transient id
     */
    protected $transient_id;

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
    protected function set_unique_id() {
        if ( ! isset( $this->transient_id ) ) $this->transient_id = uniqid( $this->prefix );
        return $this->transient_id;
    }

    /**
     * Save transient.
     * 
     * @since 0.4.4
     * @access public
     * @param string|null|false $transient_id Transient id
     * @param object $values The values to store
     * @param int $expiration Time until expiration in seconds from now
     * @return bool True if values were stored, false otherwise
     */
    public function save( $transient_id, $data, $expiration = HOUR_IN_SECONDS ) {
        if ( ! $transient_id )
            $this->transient_id = $data->ID = $transient_id = $this->set_unique_id();

        return set_transient( $transient_id, $data, $expiration );
    }

    /**
     * Get transient.
     * 
     * @since 0.4.4
     * @access public
     * @return object|bool $transient The values or false if none was found
     */
    public function get( $id = false ) {
        if ( $id ) return get_transient( $id );
        return get_transient( $this->transient_id );
    }

    /**
     * Delete transient.
     * 
     * @since 0.4.4
     * @access public
     * @return bool True if deletion was comleted, false otherwise
     */
    public function delete( $id = false ) {
        if( $id ) return delete_transient( $id );
        return delete_transient( $this->transient_id );
    }

}