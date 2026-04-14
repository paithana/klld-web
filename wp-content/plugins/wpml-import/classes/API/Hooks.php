<?php

namespace WPML\Import\API;

use WPML\LIB\WP\Hooks as WPHooks;
use WPML\Import\Helper\ImportedItems;

class Hooks implements \IWPML_Frontend_Action, \IWPML_Backend_Action, \IWPML_DIC_Action {

	const TRIGGER_ENDPOINT = 'wpml_import_trigger';
	const TRIGGER_HOOK     = 'wpml_import_process';

	/**
	 * @var Commands
	 */
	private $commands;

	/**
	 * @var ImportedItems
	 */
	private $importedItems;

	public function __construct( Commands $commands, ImportedItems $importedItems ) {
		$this->commands      = $commands;
		$this->importedItems = $importedItems;
	}

	public function add_hooks() {
		WPHooks::onAction( 'init' )
			->then( [ $this, 'handleUrlRequest' ] );

		WPHooks::onAction( self::TRIGGER_HOOK )
			->then( [ $this->commands, 'processImport' ] );
	}

	public function handleUrlRequest() {
		if ( ! $this->isValidUrlRequest() ) {
			return;
		}

		/* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		$key = sanitize_text_field( $_GET[ self::TRIGGER_ENDPOINT ] ?? '' );

		if ( ! $this->isValidKey( $key ) ) {
			wp_die( esc_html__( 'Unauthorized', 'wpml-import' ), esc_html__( 'Unauthorized', 'wpml-import' ), 401 );
		}

		if ( ! $this->hasItemsToProcess() ) {
			wp_die( esc_html__( 'No items to process', 'wpml-import' ), esc_html__( 'No Content', 'wpml-import' ), 204 );
		}

		try {
			$this->commands->processImport( 'endpoint' );

			wp_die( esc_html__( 'Import process completed', 'wpml-import' ), esc_html__( 'Success', 'wpml-import' ), 200 );
		} catch ( \Exception $e ) {
			if ( defined( 'WP_DEBUG_LOG' ) && constant( 'WP_DEBUG_LOG' ) ) {
				/* phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log */
				error_log( $e->getMessage() );
			}

			wp_die( esc_html__( 'Import process failed', 'wpml-import' ), esc_html__( 'Internal Server Error', 'wpml-import' ), 500 );
		}
	}

	/**
	 * @return bool
	 */
	private function isValidUrlRequest() {
		/* phpcs:ignore WordPress.Security.NonceVerification.Recommended */
		return isset( $_GET[ self::TRIGGER_ENDPOINT ] ) && ! empty( $_GET[ self::TRIGGER_ENDPOINT ] );
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	private function isValidKey( $key ) {
		return defined( 'WPML_IMPORT_KEY' ) && constant( 'WPML_IMPORT_KEY' ) === $key;
	}

	/**
	 * @return bool
	 */
	private function hasItemsToProcess() {
		return $this->importedItems->countPosts() > 0 || $this->importedItems->countTerms() > 0;
	}
}
