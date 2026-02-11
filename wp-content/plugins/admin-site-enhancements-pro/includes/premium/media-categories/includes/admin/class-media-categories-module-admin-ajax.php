<?php
/**
 * Admin AJAX class.
 *
 * @package Media_Categories_Module
 * @author WP Media Library
 */

/**
 * Saves settings via AJAX at Media Categories Module > Settings.
 *
 * @since   1.1.6
 */
class Media_Categories_Module_Admin_AJAX {

	/**
	 * Holds the base class object.
	 *
	 * @since   1.1.6
	 *
	 * @var     object
	 */
	public $base;

	/**
	 * Constructor
	 *
	 * @since   1.1.6
	 *
	 * @param   object $base    Base Plugin Class.
	 */
	public function __construct( $base ) {

		// Store base class.
		$this->base = $base;

	}

}
