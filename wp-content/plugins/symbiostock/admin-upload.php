<?php

$currSS_L->disable_direct_access();

$ss_Upload = new ss_create_Upload();

class ss_create_Upload {
	function ss_create_Upload() {
		add_action( 'admin_menu',  array($this, 'add_ss_upload_page' ) );
    }

	function add_ss_upload_page() {
		add_submenu_page('edit.php?post_type=product', 'Upload Media', 'Upload Media', 'manage_product_terms', 'manage_ss_upload', array($this, 'ss_upload_page' ) );
	}

	function ss_upload_page() {
		$GLOBALS['currSS_L']->issspage = 1;

		if(isset($_POST['fpass'])) {
			if (!isset($_FILES["_ss_uploadnewmedia"]) || !$GLOBALS['currSS_L']->ss_canupload) return;
			if (ss_process_upload()) {
?>
		<div class="updated">
			<p><?php print __('Your uploaded file has been added.','ss'); ?></p>
		</div>
<?php
			}
		}

		do_action('ss_web_uploader_start');

		if ($GLOBALS['currSS_L']->ss_canupload) {
			do_action('ss_upload_page_start');
?>
	<form method="post" id="mainform" action="" enctype="multipart/form-data">
<input type="hidden" name="fpass" value="1">
		<h3>Upload Media via Browser</h3>

							<p class="form-field _ss_reuploadimage_field "><img src="<?php print $GLOBALS['currSS_L']->ss_web_assets_dir."ss_ico_b.png"; ?>" style="width:24px;vertical-align:bottom;padding-right:9px;padding-bottom:3px;"><input type="file" id="_ss_uploadnewmedia" name="_ss_uploadnewmedia"> <input name="save" class="button-primary" type="submit" value="Upload Media" /><br><br>
Upload a media file here via your browser. File will be processed in real-time and may take some time to complete.
</p>
</form>
<?php
			do_action('ss_upload_page_end');

		}
		do_action('ss_web_uploader_end');
	}
}

function ss_process_upload() {
	if (!move_uploaded_file($_FILES["_ss_uploadnewmedia"]["tmp_name"], $GLOBALS['currSS_L']->ss_media_upload_dir.$_FILES["_ss_uploadnewmedia"]["name"])) return false;

	$fileloc = $GLOBALS['currSS_L']->ss_media_upload_dir.$_FILES["_ss_uploadnewmedia"]["name"];

	$title = '';
	$description = '';
	$keywords = '';
	$category = array();

	if (!$title) {
		$title = pathinfo($fileloc,PATHINFO_BASENAME);
	}

	$post = array(
		'post_author' => $GLOBALS['currSS_L']->userid,
		'post_content' => $description,
		'post_status' => 'draft',
		'post_title' => $title,
		'post_parent' => '',
		'post_type' => 'product',
	);

	//Create post
	$post_id = wp_insert_post( $post);

	if (!$post_id) {
		$err = ". Could not create product in database";
		$GLOBALS['currSS_L']->scripterr("Failed to process file: ".$fname.$err);
		rename($fileloc, $GLOBALS['currSS_L']->ss_media_upload_dir.$GLOBALS['currSS_L']->ss_media_failed_prefix.$fname);
		return false;
	}

	$finalfilename = strtolower("ss_".$post_id.'.'.pathinfo($fileloc,PATHINFO_EXTENSION));
	update_post_meta($post_id, 'ss_media_filename', $finalfilename);

	wp_set_object_terms($post_id, 'simple', 'product_type');
	update_post_meta($post_id, '_visibility', 'visible');
	update_post_meta($post_id, 'total_sales', '');
	update_post_meta($post_id, '_downloadable', 'yes' );
	update_post_meta($post_id, '_virtual', 'yes' );
	update_post_meta($post_id, '_download_limit', 3);
	update_post_meta($post_id, '_download_expiry', 7);

	update_post_meta($post_id, '_price', 20);
	update_post_meta($post_id, '_regular_price', 20);

	$url = $GLOBALS['currSS_L']->ss_media_dir_web.$finalfilename;
	$filename = substr(md5(time()),0,6).'.media';

	$file_hash = md5($url);
	$files[$file_hash] = array(
		'name' => $filename,
		'file' => $url
	);

	update_post_meta($post_id, '_downloadable_files', $files);

	rename ($fileloc, $GLOBALS['currSS_L']->ss_media_dir.$finalfilename);
	ss_attach_images($post_id,$title);

	return $post_id;
}


?>