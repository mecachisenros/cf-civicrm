<?php

/**
 * CiviCRM Caldera Forms Transient Class
 *
 * @since 0.4.4
 */
class CiviCRM_Caldera_Forms_CRM_API {

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
     * Set transient.
     * 
     * @since 0.4.4
     * @access public
     * @param string $entity The entity
     * @param string $action The action
     * @param array $params The parameters
     * @param bool $ignore Whether to ignore the catch
     * @return array|Exception The result of the API call, an error array, or an Exception
     */
    public function wrapper( $entity, $action, $params, $ignore = false ) {
        if ( empty( $entity ) || empty( $action ) || empty( $params ) )
            throw new Exception( 'One of given parameters is empty.' );
            
        try {
            $result = civicrm_api3( $entity, $action, $params );
        } catch ( CiviCRM_API3_Exception $e ) {
            if ( ! $ignore ) {
                $error = $e->getMessage() . '<br><br><pre>' . $e->getTraceAsString() . '</pre>';
                return [ 'note' => $error, 'type' => 'error' ];
            }
        }
        return $result;
    }
}