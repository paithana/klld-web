<?php

namespace WPML\Import\Integrations\WPAllExport;

use WPML\FP\Obj;
use WPML\Import\Helper\Page;

use function WPML\Container\make;

class HooksFactory implements \IWPML_Backend_Action_Loader, \IWPML_CLI_Action_Loader {

	const LABEL = 'WP All Export Pro';

	/**
	 * @return \IWPML_Action[]|null
	 */
	public function create() {
		$hooks = [];

		if ( self::isExporting() ) {
			$hooks[] = make( \WPML\Import\Integrations\Base\Strategies\Simulate\ExportPostsHooks::class );
			$hooks[] = make( \WPML\Import\Integrations\Base\Strategies\Simulate\ExportTermsHooks::class );
		}

		$hooks[] = make( PrepareFieldsHooks::class );

		if ( self::isOnExportPage() ) {
			$hooks[] = make( ExportNotice::class );
		}

		return $hooks;
	}

	/**
	 * @return bool
	 */
	private static function isExporting() {
		if (
			defined( 'DOING_AJAX' )
			&& DOING_AJAX
			/* phpcs:ignore WordPress.Security.NonceVerification.Missing */
			&& 'wpallexport' === Obj::prop( 'action', $_POST )
		) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public static function isOnExportPage() {
		return Page::isOn( '/admin.php?page=pmxe-admin-export' );
	}

	/**
	 * @return bool
	 */
	public static function hasWooCommerceAddon() {
		return defined( 'PMWE_VERSION' );
	}
}
