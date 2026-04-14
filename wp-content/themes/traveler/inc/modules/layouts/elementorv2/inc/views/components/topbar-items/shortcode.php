<?php
/**
 * Created by PhpStorm.
 * User: HanhDo
 * Date: 1/17/2019
 * Time: 11:43 AM
 */
?>
<li class="topbar-item topbar-item__shortcode dropdown">
	<?php
	$shortcode = $val['topbar_shortcode'];
	if ( ! empty( $shortcode ) ) {
		echo do_shortcode( $shortcode );
	}
	?>
</li>
