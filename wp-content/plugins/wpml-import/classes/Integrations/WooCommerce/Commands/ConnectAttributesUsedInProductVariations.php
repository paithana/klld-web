<?php

namespace WPML\Import\Integrations\WooCommerce\Commands;

use WPML\Collect\Support\Collection;
use WPML\Import\Commands\Base\Command;
use WPML\Import\Commands\Base\Query;
use WPML\Import\Commands\Base\TemporaryTermFields;
use WPML\Import\Fields;
use WPML\Import\Helper\Taxonomies;

class ConnectAttributesUsedInProductVariations implements Command, TemporaryTermFields {

	use Query;

	const DEFAULT_LIMIT = 100;

	const FIELD_TEMPORARY_ATTEMPT_RECONNECT_ATTRIBUTE = '_wpml_import_attempt_reconnect_wc_attribute';

	/**
	 * @var \wpdb $wpdb
	 */
	protected $wpdb;

	/**
	 * @var \SitePress $sitepress
	 */
	protected $sitepress;

	public function __construct( \wpdb $wpdb, \SitePress $sitepress ) {
		$this->wpdb      = $wpdb;
		$this->sitepress = $sitepress;
	}


	/**
	 * @return string
	 */
	public static function getTitle() {
		return __( 'Linking Product Attribute Translations', 'wpml-import' );
	}

	/**
	 * @return string
	 */
	public static function getDescription() {
		return __( 'Connecting product attributes to their translations based on associated product variations.', 'wpml-import' );
	}

	/**
	 * @param Collection|null $args
	 *
	 * @return int
	 */
	public function countPendingItems( Collection $args = null ) {
		return count( $this->getPendingItems() );
	}

	/**
	 * @param Collection|null $args
	 *
	 * @return int Number of processed items.
	 */
	public function run( Collection $args = null ) {
		$countProcessed  = 0;
		$defaultLanguage = $this->sitepress->get_default_language();

		foreach ( $this->getPendingItemGroups( self::DEFAULT_LIMIT ) as $groupedItems ) {
			if ( isset( $groupedItems[ $defaultLanguage ] ) ) {
				$originalAttribute = $groupedItems[ $defaultLanguage ];
			} else {
				$originalAttribute = reset( $groupedItems );
			}

			foreach ( $groupedItems as $attribute ) {
				add_term_meta( $attribute->attribute_term_id, self::FIELD_TEMPORARY_ATTEMPT_RECONNECT_ATTRIBUTE, 1 );

				if ( $attribute->attribute_ttid === $originalAttribute->attribute_ttid ) {
					continue;
				}

				$this->sitepress->set_element_language_details(
					$attribute->attribute_ttid,
					'tax_pa_' . $attribute->attribute_name,
					$originalAttribute->attribute_trid,
					$attribute->attribute_language_code,
					$originalAttribute->attribute_source_language_code
				);
			}

			$countProcessed += count( $groupedItems );
		}

		return $countProcessed;
	}

	/**
	 * @param int|null $limit
	 *
	 * @return array
	 */
	private function getPendingItems( $limit = null ) {
		$translatableTaxTypes = Taxonomies::getTranslatable( true );
		if ( empty( $translatableTaxTypes ) ) {
			return [];
		}

		$items = $this->getResultsWithLimit(
			"
			SELECT
				tt.term_taxonomy_id AS attribute_ttid,
				tt.term_id AS attribute_term_id,
				REPLACE( pmattr.meta_key, 'attribute_pa_', '' ) AS attribute_name,
				pmattr.meta_value AS attribute_value,
				ptr.trid AS product_trid,
				atr.trid AS attribute_trid,
				atr.language_code AS attribute_language_code,
				atr.source_language_code AS attribute_source_language_code
			FROM {$this->wpdb->postmeta} AS pmattr
			LEFT JOIN {$this->wpdb->terms} AS t
				ON t.slug = pmattr.meta_value
			LEFT JOIN {$this->wpdb->term_taxonomy} AS tt
				ON tt.term_id = t.term_id AND tt.taxonomy = CONCAT( 'pa_', REPLACE( pmattr.meta_key, 'attribute_pa_', '' ) )
			LEFT JOIN {$this->wpdb->termmeta} AS tm
			    ON tm.term_id = tt.term_id AND tm.meta_key = '" . self::FIELD_TEMPORARY_ATTEMPT_RECONNECT_ATTRIBUTE . "'
			LEFT JOIN {$this->wpdb->posts} AS p
				ON p.ID = pmattr.post_id
			LEFT JOIN {$this->wpdb->postmeta} AS pm
				ON pm.post_id = p.ID AND pm.meta_key = '" . Fields::TRANSLATION_GROUP . "'
			LEFT JOIN {$this->wpdb->prefix}icl_translations AS ptr
				ON ptr.element_id = p.ID AND ptr.element_type = 'post_product_variation'
			LEFT JOIN {$this->wpdb->prefix}icl_translations AS atr
				ON atr.element_id = tt.term_taxonomy_id AND atr.element_type = CONCAT( 'tax_pa_', REPLACE( pmattr.meta_key, 'attribute_pa_', '' ) )
			WHERE p.post_type = 'product_variation'
				AND pm.meta_value IS NOT NULL
				AND atr.source_language_code IS NULL
				AND tm.meta_value IS NULL
				AND atr.element_type IN(" . wpml_prepare_in( $translatableTaxTypes ) . ")
				AND pmattr.meta_key LIKE '" . $this->wpdb->esc_like( 'attribute_pa_' ) . "%'
			ORDER BY ptr.trid ASC
			",
			$limit
		);

		return $items;
	}

	/**
	 * @param int $limit
	 *
	 * @return array[]
	 */
	private function getPendingItemGroups( $limit ) {
		$items       = $this->getPendingItems( $limit );
		$isLastBatch = count( $items ) < $limit;
		$itemsGroups = [];

		foreach ( $items as $item ) {
			if ( ! array_key_exists( $item->product_trid, $itemsGroups ) ) {
				$itemsGroups[ $item->product_trid ] = [];
			}

			$itemsGroups[ $item->product_trid ][ $item->attribute_language_code ] = $item;
		}

		if ( ! $isLastBatch ) {
			array_pop( $itemsGroups ); // Remove the last group to prevent incomplete ones.
		}

		return $itemsGroups;
	}

	/**
	 * @return string[]
	 */
	public static function getTemporaryTermFields() {
		return [
			self::FIELD_TEMPORARY_ATTEMPT_RECONNECT_ATTRIBUTE,
		];
	}
}
