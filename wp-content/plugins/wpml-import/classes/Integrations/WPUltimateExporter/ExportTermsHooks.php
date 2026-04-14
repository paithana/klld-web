<?php

namespace WPML\Import\Integrations\WPUltimateExporter;

use WPML\LIB\WP\Hooks;
use WPML\Import\Integrations\Base\Languages;
use WPML\Import\Integrations\Base\Strategies\Generate\QueriedObject;

use function WPML\FP\spreadArgs;

/**
 * Note that WP Ultimate Exporter only supports exporting data and metadata for the following taxonomies:
 * - Native categories and tags.
 * - WooCommerce product categories and product tags.
 *
 * This means that there is no support for exporting custom taxonomies and their metadata.
 *
 * This integration, thus, will work for the supported items.
 */

// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
class ExportTermsHooks extends \WPML\Import\Integrations\Base\Strategies\Generate\ExportTermsHooks {
	use Languages;
	use QueriedObject;
	use MetaHeaders;

	public function add_hooks() {
		Hooks::onFilter( 'get_terms_args' )->then( spreadArgs( [ $this, 'includeAllLanguagesInQuery' ] ) );
		Hooks::onFilter( 'query' )->then( spreadArgs( [ $this, 'setTermMetaFields' ] ) );
		parent::add_hooks();
	}

	/**
	 * @param  string $query
	 *
	 * @return string
	 */
	public function setTermMetaFields( $query ) {
		if ( $this->isMetaQuery( $query ) ) {
			$this->setMetaHeaders();
			$this->setMetaFields( $this->getQueriedTerm( $query ) );
		}
		return $query;
	}

	/**
	 * @return string
	 */
	protected function getQuerySignature() {
		return $this->wpdb->prepare( "SELECT wp.term_id,meta_key,meta_value FROM {$this->wpdb->prefix}terms wp JOIN {$this->wpdb->prefix}termmeta wpm ON wpm.term_id = wp.term_id where meta_key NOT IN (%s,%s) AND wp.term_id = ", '_edit_lock', '_edit_last' );
	}
}
// phpcs:enable
