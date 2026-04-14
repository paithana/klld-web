<?php

namespace WPML\Import\Helper;

use function WPML\FP\partial;

class Resources {

	/**
	 * @param string $app
	 *
	 * @return callable|\Closure
	 *
	 * @codeCoverageIgnore
	 */
	public static function enqueueApp( $app ) {
		return partial(
			[ '\WPML\LIB\WP\App\Resources', 'enqueue' ],
			$app,
			WPML_IMPORT_PLUGIN_URL,
			WPML_IMPORT_PLUGIN_PATH,
			WPML_IMPORT_VERSION,
			'wpml-import'
		);
	}
}
