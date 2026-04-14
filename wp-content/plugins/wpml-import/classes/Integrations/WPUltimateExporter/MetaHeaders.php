<?php

namespace WPML\Import\Integrations\WPUltimateExporter;

use WPML\Import\Integrations\Base\Fields;

trait MetaHeaders {
	use Fields;

	private function setMetaHeaders() {
		if ( ! class_exists( '\Smackcoders\SMEXP\ExportExtension' ) ) {
			return;
		}
		if ( ! is_callable( [ '\Smackcoders\SMEXP\ExportExtension', 'getInstance' ] ) ) {
			return;
		}
		$extension          = \Smackcoders\SMEXP\ExportExtension::getInstance();
		$existingHeaders    = $extension->headers;
		$importFields       = $this->getImportFields();
		$extension->headers = array_unique( array_merge( $existingHeaders, $importFields ) );
	}
}
