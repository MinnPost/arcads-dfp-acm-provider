<?php
/**
 * Automatically loads the specified file.
 */

/**
 * Enable autoloading of plugin classes
 *
 * @param $class_name
 */
spl_autoload_register(
	function ( $class_name ) {

		// Only autoload classes from this plugin
		if ( 'ArcAds_DFP_ACM_Provider' !== $class_name && 0 !== strpos( $class_name, 'ArcAds_DFP_ACM_Provider' ) ) {
			return;
		}

		// wpcs style filename for each class
		$file_name = 'class-' . str_replace( '_', '-', strtolower( $class_name ) );

		// create file path
		$file = dirname( ARCADS_DFP_ACM_PROVIDER_FILE ) . '/php/' . $file_name . '.php';

		// If a file is found, load it
		if ( file_exists( $file ) ) {
			require_once $file;
		}

	}
);
