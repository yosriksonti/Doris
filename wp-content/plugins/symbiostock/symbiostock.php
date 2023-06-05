<?php
/**
 * Plugin Name: Symbiostock Lite
 * Plugin URI: http://www.symbiostock.org
 * Description: Symbiostock Lite allows artists, illustrators and photographers to sell their photographs and stock images online quickly and easily.
 * Version: 3.4.0
 * Author: Symbiostock
 * Author URI: http://www.symbiostock.org
 * Text Domain: ss
 * License: GPLv2
 */

if (!defined('ABSPATH')) exit(); 

include_once(trailingslashit(plugin_dir_path(__FILE__)).'ss-env-check.php');

if (!@$GLOBALS["currSSEC"]->sspro) {
	$currSS_L = new ss_l_Helper();
	add_action( 'woocommerce_init', 'ss_l_go',11);
}

function ss_l_go() {
	global $currSS_L;

	do_action( 'ss_before_dependencies_check' );

	// Check to make sure environment is sound - if so, proceed loading Symbiostock
	if (!$GLOBALS["currSSEC"]->missingdependencies) {

		//check for processor
		if ((isset($_GET['ss_c']) && ($_GET['ss_c'] == get_option('_cron_code'))) || (isset($_POST['ss_c']) && ($_POST['ss_c'] == get_option('_cron_code')))) define('ss_cron', 1);

		//check for download
		if (isset( $_GET['download_file'] ) && isset( $_GET['order'] ) && isset( $_GET['email'] )) define('ss_download', 1);

		//check for Symzio
		if (isset( $_GET['_sz'])) define('ss_sz', 1);

		do_action( 'ss_before_includes_check' );

		if (is_admin() || defined('ss_cron') || defined('ss_download') || defined('ss_sz')) {
			@set_time_limit(600);

			do_action( 'ss_start_core_includes' );

			require_once($currSS_L->ss_rootdir.'symbiostock-init.php' );	// Initialize Symbiostock for first load
			require_once($currSS_L->ss_rootdir.'admin-global.php' );	// Change global interface look
			require_once($currSS_L->ss_rootdir.'tools-imagemanipulation.php' );	// Add tools for manipulating images
			require_once($currSS_L->ss_rootdir.'admin-upload.php' );	// Web Upload new files

			do_action('ss_end_core_includes');
		}

		do_action( 'ss_public_includes' );

		// SS Ping
		require_once($currSS_L->ss_rootdir.'front-ping.php' );	// Provide pingback for SS

		do_action( 'ss_end_core_init' );
	}

	do_action( 'ss_init' );
}

class ss_l_Helper {
	function __construct() {
		$this->disable_direct_access();

		$curr = wp_upload_dir();
		$this->ss_upload_dir = trailingslashit($curr['basedir']);
		$this->ss_web_upload_dir = trailingslashit($curr['baseurl']);

		// Initiation of globally used variables
		$this->ss_rootdir = trailingslashit(plugin_dir_path(__FILE__));
		$this->ss_assets_dir = $this->ss_rootdir . 'assets/';

		$this->ss_media_dir = $this->ss_upload_dir.'ss_l_media/';
		$this->ss_media_dir_web = $this->ss_web_upload_dir.'ss_l_media/';

		$this->ss_tmp_dir = $this->ss_media_dir.'tmp/';
		$this->ss_media_upload_dir = $this->ss_media_dir . 'new/';
		$this->ss_web_plugin_dir = trailingslashit(plugins_url('symbiostock'));
		$this->ss_web_assets_dir = $this->ss_web_plugin_dir . 'assets/';
		$this->ss_watermark_loc_old = $this->ss_rootdir . 'assets/watermark.png';
		$this->ss_watermark_loc = $this->ss_upload_dir . 'watermark.png';
		$this->ss_default_watermark_loc = $this->ss_rootdir . 'assets/ss_watermark.png';
		$this->ss_media_replace_prefix = '_ssrep_';
		$this->ss_media_alt_prefix = '_ssalt_';
		$this->ss_media_failed_prefix = '_ss_failed_';
		$this->ss_canupload = 1;

		if (get_option('_cron_code')) $this->ss_cron_loc = trailingslashit(site_url()).'?c=1&ss_c='.get_option('_cron_code');

		// required to prevent imagick from crashing when ratio is so lopsided that it makes one dimension 0 - expecting that 100:1 is a safe minimum
		$this->ss_minimum_img_scale = 80;

		// In the case an item is sold that is not raster and doesn't have x/y limits on size
		$this->ss_fallback_imagesize = 6000;

		$this->userid = 1;
		$this->siteid = 1;

		do_action( 'ss_helper_init' );
	}

	// End scripting if accessed directly
	function disable_direct_access() {
		do_action( 'ss_direct_access_check' );

		if (!defined('ABSPATH')) exit(); 
	}

	// All the update notice functions
	function admin_notice_received() {
		?>
		<div class="updated">
			<?php print __('Your changes have been saved.','ss'); ?>
		</div>
		<?php
	}

	function admin_notice_unknown() {
		?>
		<div class="updated">
			<?php print __('An update occurred. No clue why.','ss'); ?>
		</div>
		<?php
	}

	function error_notice($err="unknown") {
		add_action('admin_notices',array($this,'error_notice_'.$err));
	}

	function error_notice_unknown() {
		?>
		<div class="error" style="padding: 11px 15px;">
			<?php print __('An error occurred. No clue why.','ss'); ?>
		</div>
		<?php
	}
}

if (!@$GLOBALS["currSSEC"]->sspro) add_filter( 'plugin_row_meta', 'ss_l_plugin_row_meta', 10, 2 );
function ss_l_plugin_row_meta( $links, $file ) {

	if ( strpos( $file, 'symbiostock.php' ) !== false ) {
		$new_links = array('upgrade' => '<a href="http://www.symbiostock.org/shop/" target="_blank"><B>Upgrade to Pro for Free</b></a>');
		$links = array_merge( $links, $new_links );
	}
	
	return $links;
}
?>