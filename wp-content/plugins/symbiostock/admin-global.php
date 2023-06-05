<?php

$currSS_L->disable_direct_access();

//Symbiostock Branding

// Change titles
add_filter( 'admin_footer_text', 'ss_footer_text',999);
function ss_footer_text($footer_text) {
	if (isset($GLOBALS['currSS_L']->issspage) || stristr($footer_text,'woocommerce')) return '<a href="http://www.symbiostock.org/docs/" target="_blank">Documentation</a> | Thank you for selling with Symbiostock and WooCommerce. Please leave us a <a href="https://wordpress.org/support/view/plugin-reviews/symbiostock#postform" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> rating if you find Symbiostock useful!';
	return $footer_text;
}

?>