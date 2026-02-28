<?php

namespace Larastech\Booking\Database;

/**
 * Base Repository class.
 */
abstract class Repository {

	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $table_name;

	/**
	 * Constructor.
	 */
	public function __construct( $table_name ) {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'larastech_' . $table_name;
	}

	/**
	 * Find a record by ID.
	 */
	public function find( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->table_name} WHERE id = %d", $id ) );
	}

	/**
	 * Insert a record.
	 */
	public function insert( $data ) {
		global $wpdb;
		return $wpdb->insert( $this->table_name, $data );
	}

	/**
	 * Update a record.
	 */
	public function update( $id, $data ) {
		global $wpdb;
		return $wpdb->update( $this->table_name, $data, [ 'id' => $id ] );
	}

	/**
	 * Delete a record.
	 */
	public function delete( $id ) {
		global $wpdb;
		return $wpdb->delete( $this->table_name, [ 'id' => $id ] );
	}
}
