<?php

namespace WPML\Import\Integrations\WPUltimateExporter;

use WPML\FP\Obj;

use function WPML\Container\make;

class HooksFactory implements \IWPML_Backend_Action_Loader, \IWPML_CLI_Action_Loader {

	/**
	 * @return \IWPML_Action[]|null
	 */
	public function create() {
		$hooks = [];

		if ( self::isExporting() ) {
			$hooks[] = make( ExportPostsHooks::class );
			$hooks[] = make( ExportTermsHooks::class );
		}

		if ( self::isCounting() ) {
			$hooks[] = make( CountObjectsHooks::class );
		}

		return $hooks;
	}

	/**
	 * @param  string $action
	 *
	 * @return bool
	 */
	private function isAjaxAction( $action ) {
		if (
			defined( 'DOING_AJAX' )
			&& DOING_AJAX
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			&& Obj::prop( 'action', $_POST ) === $action
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			&& Obj::prop( 'module', $_POST ) !== null
		) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	private function isExporting() {
		return $this->isAjaxAction( 'parse_data' );
	}

	/**
	 * @return bool
	 */
	private function isCounting() {
		return $this->isAjaxAction( 'total_records' );
	}
}
