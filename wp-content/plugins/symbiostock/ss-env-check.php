<?php

// Symbiostock Pro
// @copyright Copyright (c) 2015, Symbiostock

if (!class_exists('ssEnvCheck')) {

class ssEnvCheck {
	function __construct() {
		$this->woover = "3.4.4";
		$this->ssver = "3.4.0";
		$this->minphp = 50300;
		$this->maxphp = 70103;

		include_once(ABSPATH.'wp-admin/includes/plugin.php');

		if (is_plugin_active('symbiostock/symbiostock.php')) $this->sslite = 1;
		if (is_plugin_active('symbiostock-pro/symbiostock-pro.php')) $this->sspro = 1;
		if (is_plugin_active('woocommerce/woocommerce.php')) $this->woo = 1;

		// Check for Symbiostock Lite
		if (!@$this->sslite) {
			$this->missingdependencies = 1;
			$this->error_notice("nosslite");
			$this->haserror = 1;
		}

		// Check for Woocommerce
		if (!@$this->woo) {
			$this->missingdependencies = 1;
			$this->error_notice("nowoo");
			$this->haserror = 1;
		} else {
			$ver = get_plugin_data(ABSPATH.'wp-content/plugins/woocommerce/woocommerce.php');
			if (($ver["Version"] != $this->woover) && !get_option('ss_ignorewrongwoo') && !isset($_GET['ss_ignorewrongwoo'])) {
				$this->wrongwoo = $ver["Version"];
				$this->error_notice("wrongwoo");
			}
		}

		// Check for Imagick
		if(!extension_loaded('imagick') && !get_option('ss_ignoreimagick') && !isset($_GET['ss_ignoreimagick'])) {
			$this->error_notice("noimagick");
			$this->haserror = 1;
		}

		// Check PHP version
		if (!get_option('ss_ignorephpver') && ((PHP_VERSION_ID > $this->maxphp) || (PHP_VERSION_ID < $this->minphp))) {
			$this->error_notice("phpver");
			$this->haserror = 1;
		}

		// Show quick guide (lite)
		if (!@$this->sspro && !isset($this->haserror) && !get_option('ss_ignoresspro') && !isset($_GET['ss_ignoresspro'])) {
			$this->error_notice("litetopro");
		}
		if (!@$this->sspro && !isset($this->haserror) && !get_option('ss_l_ignorequickguide') && !isset($_GET['ss_l_ignorequickguide'])) {
			$this->error_notice("litequickguide");
		}

		// Show quick guide (pro)
		if (@$this->sspro && !isset($this->haserror) && !get_option('ss_ignorequickguide') && !isset($_GET['ss_ignorequickguide'])) {
			$this->error_notice("proquickguide");
		}

		// Show Symzio prompt (pro)
		if (@$this->sspro && !isset($this->haserror) && !get_option('ss_sz_ppass') && get_option('ss_sz_active')) {
			$this->error_notice("nosymzio");
		}

		if (@$this->sspro) {
			// check for stores errors
			$this->error_notice("stored");
		}

		add_action('admin_notices',array($this,'error_notice_run'));
	}

	function error_notice($err="unknown") {
		$this->errornotices[] = $err;
	}

	function error_notice_run() {
		if (!current_user_can('administrator')) return false;
		if (!count($this->errornotices)) return false;
		foreach ($this->errornotices as $err) $this->{'error_notice_'.$err}();
	}

	// All the error functions - must be explicitly coded for use with 'add_action' function/filter
	function error_notice_stored() {
		if (!$errs = get_option('ss_errs')) return false;
		if (!count($errs)) return false;
		?>
		<div class="error" style="padding: 11px 15px;">
<img src="<?php print $GLOBALS['currSS']->ss_web_assets_dir."ss_ico_b.png"; ?>" style="width:24px;vertical-align:middle;padding-right:9px;padding-bottom:3px;"><b>Error(s):</b>
<?php
foreach ($errs as $value) print $value." <span style='font-weight:bold;font-size:20px;'>|</span> ";
print '<a href="'.admin_url( 'edit.php?post_type=product&ss_purgerrs=1').'">Dismiss error(s)</a>.';
?>
		</div>
		<?php
	}

	function error_notice_litetopro() {
?>
<div class="notice notice-info">
<p><table cellspacing="0" cellpadding="0" border="0"><tr><td style="padding-right:10px;"><span class="dashicons dashicons-thumbs-up" style="width:40px;height:40px;font-size:40px;"></span></td><td>Upgrade to <a href="https://www.symbiostock.org/shop/" target="_blank"><B>Symbiostock Pro</b></a> for <B>free</b>! Custom watermarks, FTP uploads, licensing, vectors and more are just some of the advanced features available. For more information about Symbiostock Pro vs Lite go <a href="https://www.symbiostock.org/docs/pro-vs-lite/" target="_blank">here</a>. Click <a href="<?php print admin_url( 'edit.php?post_type=product&ss_ignoresspro=1'); ?>">here</a> to ignore</td></tr></table></p>
</div>
<?php
	}

	function error_notice_nowoo() {
		?>
		<div class="error" style="padding: 11px 15px;">
			<?php print __('Symbiostock requires WooCommerce '.$this->woover.' to run. Click <a href="'.admin_url('index.php?page=ss_updates&installwoo=1').'">here</a> to install & activate.','ss'); ?>
		</div>
		<?php
	}

	function error_notice_wrongwoo() {
		?>
		<div class="update-nag" style="padding: 11px 15px;">
			<?php print __('Symbiostock requires WooCommerce '.$this->woover.' to run. You are currently running a different version ('.$this->wrongwoo.') and may experience unknown quirks as a result. Please install <a href="https://downloads.wordpress.org/plugin/woocommerce.'.$this->woover.'.zip" target="_blank">WooCommerce '.$this->woover.'</a>. If your current version is higher than '.$this->woover.', please refer to <a href="https://www.symbiostock.org/forums/topic/current-woocommerce-version/">this guide</a> on downgrading. Click <a href="'.admin_url( 'edit.php?post_type=product&ss_ignorewrongwoo=1').'">here</a> to ignore this warning.','ss'); ?>
		</div>
		<?php
	}

	function error_notice_nosslite() {
		?>
		<div class="error" style="padding: 11px 15px;">
			<?php print __('Symbiostock Pro requires Symbiostock Lite to run. Click <a href="'.admin_url('index.php?page=ss_updates&installlite=1').'">here</a> to install & activate.','ss'); ?>
		</div>
		<?php
	}

	function error_notice_phpver() {
		?>
		<div class="update-nag">
			<?php print __('Symbiostock has been verified with PHP 5.3 to 7.1. You are currently running version '.phpversion().'. We suggest changing to a verified version to avoid bugs. Click <a href="'.admin_url( 'edit.php?post_type=product&ss_ignorephpver=1').'">here</a> to ignore this warning.','ss'); ?>
		</div>
		<?php
	}

	function error_notice_noimagick() {
		?>
		<div class="update-nag">
			<?php print __('Symbiostock strongly recommends the Imagick PHP extension for use. Please install or enable Imagick. Click <a href="'.admin_url( 'edit.php?post_type=product&ss_ignoreimagick=1').'">here</a> to ignore this warning and continue using the limited GD library for JPEGs only.','ss'); ?>
		</div>
		<?php
	}

	function error_notice_proquickguide() {
		?>
		<div class="update-nag">
			<?php print __('Check out our <a href="https://www.symbiostock.org/docs/3-minute-guide-to-launching-your-store/" target="_blank">3 minute getting started guide</a> to start using Symbiostock Pro. Click <a href="'.admin_url( 'edit.php?post_type=product&ss_ignorequickguide=1').'">here</a> to hide this notice.','ss'); ?>
		</div>
		<?php
	}

	function error_notice_litequickguide() {
		?>
		<div class="update-nag">
			<?php print __('Check out our <a href="https://www.symbiostock.org/docs/symbiostock-lite-quick-guide/" target="_blank">quick guide</a> to start using Symbiostock Lite. Click <a href="'.admin_url( 'edit.php?post_type=product&ss_l_ignorequickguide=1').'">here</a> to hide this notice.','ss'); ?>
		</div>
		<?php
	}

	function error_notice_nosymzio() {
		?>
		<div class="update-nag">
			<?php print __('Symzio has not yet crawled your Symbiostock site. Once your site is ready, apply to become a <a href="http://www.symzio.com/" target="_blank">Symzio contributor</a>. You can disable this prompt by disabling Symzio in your <a href="'.admin_url( 'edit.php?post_type=product&page=manage_ss_sz_settings').'">settings</a>.','ss'); ?>
		</div>
		<?php
	}
}

if (!isset($GLOBALS["currSSEC"])) $GLOBALS["currSSEC"] = new ssEnvCheck();

add_action('ss_end_core_init', 'ss_env_check_update_options');
function ss_env_check_update_options() {
	if (!current_user_can('administrator')) return;

	// Clear ignored warnings
	if (isset($_GET['ss_ignoreimagick'])) update_option('ss_ignoreimagick',1);
	if (isset($_GET['ss_l_ignorequickguide'])) update_option('ss_l_ignorequickguide',1);
	if (isset($_GET['ss_ignorequickguide'])) update_option('ss_ignorequickguide',1);
	if (isset($_GET['ss_ignorephpver'])) update_option('ss_ignorephpver',1);
	if (isset($_GET['ss_ignorewrongwoo'])) update_option('ss_ignorewrongwoo',1);
	if (isset($_GET['ss_ignoresspro'])) update_option('ss_ignoresspro',1);

	if (isset($_GET['ss_purgerrs'])) {
		update_option('ss_errs',array());
		header("Location: ".admin_url( 'edit.php?post_type=product'));
		exit();
	}
}

if (isset($_GET["page"]) && ($_GET["page"] == 'ss_updates')) {
add_action('admin_menu', 'add_ss_updates_page');

function add_ss_updates_page() {
  add_submenu_page('index.php', 'symbiostock-updates', 'Symbiostock Updates', 'manage_options', 'ss_updates', 'ss_updates_page');
}

function ss_updates_page() {
	include_once(ABSPATH.'wp-admin/includes/class-wp-upgrader.php');
	if (isset($_GET["installwoo"])) {
		$upgrader = new Plugin_Upgrader();
		if ($upgrader->install('https://downloads.wordpress.org/plugin/woocommerce.'.$GLOBALS["currSSEC"]->woover.'.zip') === true) {
			echo "<p>Activating plugin...</p>";
			$result = activate_plugin( 'woocommerce/woocommerce.php' );
			if ( is_wp_error( $result ) ) {
				echo "<p>".$result->get_error_message()."</p>";
			} else {
				echo "<p>Plugin activated successfully.</p>";
			}
		}
	}

	if (isset($_GET["installlite"])) {
		$upgrader = new Plugin_Upgrader();
		if ($upgrader->install('https://downloads.wordpress.org/plugin/symbiostock.'.$GLOBALS["currSSEC"]->ssver.'.zip') === true) {
			echo "<p>Activating plugin...</p>";
			$result = activate_plugin( 'symbiostock/symbiostock.php' );
			if ( is_wp_error( $result ) ) {
				echo "<p>".$result->get_error_message()."</p>";
			} else {
				echo "<p>Plugin activated successfully.</p>";
			}
		}
	}
}
}

}