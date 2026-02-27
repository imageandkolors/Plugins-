<?php

namespace Larastech\Booking\Common;

/**
 * Settings handler class.
 */
class Settings {

	/**
	 * Get a setting value.
	 *
	 * @param string $key The setting key.
	 * @param mixed  $default Default value if not found.
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		global $wpdb;
		$table = $wpdb->prefix . 'larastech_settings';

		$value = $wpdb->get_var( $wpdb->prepare(
			"SELECT option_value FROM $table WHERE option_name = %s",
			$key
		) );

		if ( null === $value ) {
			return $default;
		}

		$maybe_json = json_decode( $value, true );
		return ( null !== $maybe_json ) ? $maybe_json : $value;
	}

	/**
	 * Update a setting value.
	 *
	 * @param string $key The setting key.
	 * @param mixed  $value The value to store.
	 * @return bool
	 */
	public static function update( $key, $value ) {
		global $wpdb;
		$table = $wpdb->prefix . 'larastech_settings';

		if ( is_array( $value ) || is_object( $value ) ) {
			$value = json_encode( $value );
		}

		$result = $wpdb->replace(
			$table,
			[
				'option_name'  => $key,
				'option_value' => $value,
			],
			[ '%s', '%s' ]
		);

		return ( false !== $result );
	}
}
