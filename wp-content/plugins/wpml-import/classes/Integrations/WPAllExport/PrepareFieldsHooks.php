<?php

namespace WPML\Import\Integrations\WPAllExport;

use WPML\FP\Just;
use WPML\FP\Logic;
use WPML\FP\Obj;
use WPML\LIB\WP\Hooks;
use WPML\FP\Str;
use WPML\Import\Integrations\Base\Fields;

use function WPML\FP\pipe;
use function WPML\FP\spreadArgs;

class PrepareFieldsHooks implements \IWPML_Backend_Action, \IWPML_DIC_Action {
	use Fields;

	/**
	 * Use 11 as priority so WP All Export addons set their own field groups.
	 * This is a defensive priority setting.
	 */
	const REGISTER_FIELDS_PRIORITY = 11;

	public function add_hooks() {
		Hooks::onAction( 'pmxe_init_addons' )->then( [ $this, 'initMetaFields' ] );
	}

	/**
	 * Runs every time that the XmlExportEngine class gets instantiated.
	 *
	 * This ensures that our fields are included/offered on both export modes:
	 * - when exporting the default fields.
	 * - when offering fields to export (including them in the list of selected by default).
	 */
	public function initMetaFields() {
		Hooks::onFilter( 'wp_all_export_available_data', self::REGISTER_FIELDS_PRIORITY )
			->then( spreadArgs( [ $this, 'registerMetaFields' ] ) );
	}

	/**
	 * The relevant filter is used to initialize and set fields for posts,
	 * but also for terms, comments, users, and eventually any other type.
	 *
	 * We only want to offer and include fields for posts and terms,
	 * and the default fields for each object type include at least one prefixed item.
	 *
	 * @param  array $availableData
	 *
	 * @return bool
	 */
	private function canRegisterMetaFields( $availableData ) {
		/**
		 * @var \Closure(array):bool $hasFieldTypeStartingWithPostOrTerm
		 */
		$hasFieldTypeStartingWithPostOrTerm = pipe(
			Obj::prop( 'type' ),
			Logic::anyPass(
				[
					Str::startsWith( 'post_' ),
					Str::startsWith( 'term_' ),
				]
			)
		);

		return (bool) wpml_collect( (array) Obj::prop( 'default_fields', $availableData ) )
			->first( $hasFieldTypeStartingWithPostOrTerm );
	}

	/**
	 * Offer WPML import fields only for export types that can have them: posts and terms.
	 *
	 * Include the fields in the following groups:
	 * - init_fields: the ones that get included by default when manually setting the fields to export.
	 * - default_fields: the ones exported when exporting automatically without selecting fields.
	 * - existing_meta_keys: list of post/term meta keys to offer when manually exporting fields.
	 *
	 * As a result, in the page to select fields to export, our fields will appear under two groups:
	 * the list of default fields and the list of available fields.
	 *
	 * @param  array $availableData
	 *
	 * @return array
	 */
	public function registerMetaFields( $availableData ) {
		if ( ! $this->canRegisterMetaFields( $availableData ) ) {
			return $availableData;
		}

		/**
		 * @param array  $availableData
		 * @param string $group
		 *
		 * @return array
		 */
		$initGroupArray = function ( $availableData, $group ) {
			if (
				! array_key_exists( $group, $availableData )
				|| ! is_array( $availableData[ $group ] )
			) {
				$availableData[ $group ] = [];
			}

			return $availableData;
		};

		/**
		 * @param string $group
		 *
		 * @return \Closure(array):array
		 */
		$addFieldDefinitionsToGroup = function ( $group ) use ( $initGroupArray ) {
			return function ( $availableData ) use ( $group, $initGroupArray ) {
				$availableData = $initGroupArray( $availableData, $group );

				foreach ( $this->getImportFields() as $field ) {
					$availableData[ $group ][] = [
						'label' => $field,
						'name'  => $field,
						'type'  => 'cf',
						'auto'  => true,
					];
				}

				return $availableData;
			};
		};

		/**
		 * @param string $group
		 *
		 * @return \Closure(array):array
		 */
		$addFieldKeysToGroup = function ( $group ) use ( $initGroupArray ) {
			return function ( $availableData ) use ( $group, $initGroupArray ) {
				$availableData = $initGroupArray( $availableData, $group );

				$availableData[ $group ] = array_unique(
					array_merge( $availableData[ $group ], $this->getImportFields() )
				);

				return $availableData;
			};
		};

		// TODO Review this, it is generating double fields in the export!
		return Just::of( $availableData )
			->map( $addFieldDefinitionsToGroup( 'init_fields' ) )
			->map( $addFieldDefinitionsToGroup( 'default_fields' ) )
			->map( $addFieldKeysToGroup( 'existing_meta_keys' ) )
			->get();
	}
}
