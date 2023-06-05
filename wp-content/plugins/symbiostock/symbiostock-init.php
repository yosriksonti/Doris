<?php

$currSS_L->disable_direct_access();

// SYMBIOSTOCK INITIALIZATION

add_action('ss_end_core_init','ss_init_directories');
function ss_init_directories() {
	do_action('ss_start_init_directories');

	// Clear ignored warnings
	if (isset($_GET['ss_ignoreimagick'])) update_option('ss_ignoreimagick',1);
	if (isset($_GET['ss_ignoresspro'])) update_option('ss_ignoresspro',1);
	if (isset($_GET['ss_ignorephpver'])) update_option('ss_ignorephpver',1);
	if (isset($_GET['ss_ignorewrongwoo'])) update_option('ss_ignorewrongwoo',1);

	if (isset($_GET['ss_purgerrs'])) {
		update_option('ss_errs',array());
		header("Location: ".admin_url( 'edit.php?post_type=product'));
		exit();
	}

	// Initialize media and media upload dir
	if (!file_exists($GLOBALS['currSS_L']->ss_media_dir)) wp_mkdir_p($GLOBALS['currSS_L']->ss_media_dir);
	if (!file_exists($GLOBALS['currSS_L']->ss_media_dir.'.htaccess')) {
		$fp = fopen($GLOBALS['currSS_L']->ss_media_dir . '.htaccess', 'w');
		fwrite($fp, 'deny from all
AllowOverride None');
		fclose($fp);
	}
	if (!file_exists($GLOBALS['currSS_L']->ss_media_upload_dir)) wp_mkdir_p($GLOBALS['currSS_L']->ss_media_upload_dir);
	if (file_exists($GLOBALS['currSS_L']->ss_media_upload_dir.'.htaccess')) {
		unlink($GLOBALS['currSS_L']->ss_media_upload_dir.'.htaccess');
	}
	if (!file_exists($GLOBALS['currSS_L']->ss_tmp_dir)) wp_mkdir_p($GLOBALS['currSS_L']->ss_tmp_dir);
	if (!file_exists($GLOBALS['currSS_L']->ss_tmp_dir.'.htaccess')) {
		$fp = fopen($GLOBALS['currSS_L']->ss_tmp_dir . '.htaccess', 'w');
		fwrite($fp, 'deny from all
AllowOverride None');
		fclose($fp);
	}

	// Ensure watermark exists. If not, reset.
	if (!file_exists($GLOBALS['currSS_L']->ss_watermark_loc) && file_exists($GLOBALS['currSS_L']->ss_watermark_loc_old)) copy($GLOBALS['currSS_L']->ss_watermark_loc_old, $GLOBALS['currSS_L']->ss_watermark_loc);
	elseif (!file_exists($GLOBALS['currSS_L']->ss_watermark_loc)) copy($GLOBALS['currSS_L']->ss_default_watermark_loc, $GLOBALS['currSS_L']->ss_watermark_loc);

	do_action('ss_end_init_directories');
}

// Initial startup only - create licenses and set initial settings

add_action('init', 'ss_init_base_settings');

function ss_init_base_settings() {
		if (get_option('ss_init_base_settings_done_lite')) return;
		if (get_option('ss_init_base_settings_done')) return;

		update_option( 'woocommerce_email_footer_text', get_option('woocommerce_email_from_name').' - Powered by Symbiostock/WooCommerce');

		update_option('ss_init_base_settings_done_lite', 1);

		$curr = array( 'width' => 300, 'height' => 300, 'crop' => 0);
		update_option( 'shop_catalog_image_size', $curr );

		$curr = array( 'width' => 623, 'height' => 600, 'crop' => 0);
		update_option( 'shop_single_image_size', $curr );

		$curr = array( 'width' => 200, 'height' => 200, 'crop' => 0);
		update_option( 'shop_thumbnail_image_size', $curr );

		update_option( 'woocommerce_default_catalog_orderby', 'date' );
		update_option( 'woocommerce_manage_stock', 'no' );
		update_option( 'woocommerce_stock_format', 'no_amount' );
		update_option( 'woocommerce_calc_shipping', 0 );
		update_option( 'woocommerce_enable_shipping_calc', 0 );
		update_option( 'woocommerce_default_country', 'AU:WA' );
		update_option( 'woocommerce_currency', 'USD' );
		update_option( 'woocommerce_review_rating_verification_required', 'yes' );
		update_option( 'woocommerce_enable_review_rating','no');
		update_option( 'woocommerce_file_download_method', 'force');

		update_option( 'woocommerce_admin_footer_text_rated',1);
}

?>