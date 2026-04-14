<?php

namespace WPML\Import\Commands;

use WPML\Collect\Support\Collection;

class FlushTranslationsCache implements Base\Command {

	public static function getTitle(): string {
		return __( 'Clearing Translations Cache', 'wpml-import' );
	}

	public static function getDescription(): string {
		return __( 'Invalidating the persistent cache so it can be regenerated.', 'wpml-import' );
	}

	public function countPendingItems( Collection $args = null ): int {
		return 1;
	}

	public function run( Collection $args = null ): int {
		if (
			class_exists( \WPML_WP_Cache::class )
			&& defined( 'WPML_ELEMENT_TRANSLATIONS_CACHE_GROUP' )
		) {
			( new \WPML_WP_Cache( WPML_ELEMENT_TRANSLATIONS_CACHE_GROUP ) )->flush_group_cache();
		}

		return 1;
	}
}
