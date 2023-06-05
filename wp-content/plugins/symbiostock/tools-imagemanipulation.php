<?php

$currSS_L->disable_direct_access();

function ss_make_watermark($fileurl) {

	$curr = get_option('shop_single_image_size');
	$width = $curr['width'];
	$height = $curr['height'];
	$crop = $curr['crop'];
	$watermarkloc = $GLOBALS['currSS_L']->ss_watermark_loc;
	$boxpercent = 65;

	$image = new Imagick();
	if (method_exists($image,'setResolution')) $image->setResolution(600,600);

	try {
		if (!$image->readImage($fileurl)) return false;
    } catch (ImagickException $e) {
		$GLOBALS['currSS_L']->scripterr("Failed to read image for watermarking: ".$fileurl);
		return false;
    }
	
	if (!method_exists($image,'setResolution') && method_exists($image,'setImageResolution')) $image->setImageResolution(600,600);

	if ($watermarkloc) {
		$watermark = new Imagick();
		if (method_exists($watermark,'setResolution')) $watermark->setResolution(600,600);

		try {
			if (!$watermark->readImage($watermarkloc)) return false;
		} catch (ImagickException $e) {
			$GLOBALS['currSS_L']->scripterr("Failed to read watermark for watermarking: ".$watermarkloc);
			return false;
		}
		
		if (!method_exists($watermark,'setResolution') && method_exists($watermark,'setImageResolution')) $watermark->setImageResolution(600,600);
	}

	// Ensure width and height of target product image is not too small
	if ($width < $GLOBALS['currSS_L']->ss_minimum_img_scale) $width = $GLOBALS['currSS_L']->ss_minimum_img_scale;
	if ($height < $GLOBALS['currSS_L']->ss_minimum_img_scale) $height = $GLOBALS['currSS_L']->ss_minimum_img_scale;

	// Resize image for product image
	if (!$crop) $image->thumbnailImage($width, $height, true);
	else $image->cropThumbnailImage($width, $height);

	$iWidth = $image->getImageWidth();
	$iHeight = $image->getImageHeight();

	if ($watermarkloc) {
		// get watermark box
		if ($boxpercent > 100) $boxpercent = 100;
		if ($boxpercent < 1) $boxpercent = 1;
		$twidth = round($boxpercent*$iWidth/100);
		$theight = round($boxpercent*$iHeight/100);

		// Fit watermark in that box
		$wWidth = $watermark->getImageWidth();
		$wHeight = $watermark->getImageHeight();

		if ($wWidth/$wHeight > $twidth/$theight) {
			if ($twidth < $GLOBALS['currSS_L']->ss_minimum_img_scale) $twidth = $GLOBALS['currSS_L']->ss_minimum_img_scale;
			$watermark->scaleImage($twidth, 0);
		}
		else {
			if ($theight < $GLOBALS['currSS_L']->ss_minimum_img_scale) $theight = $GLOBALS['currSS_L']->ss_minimum_img_scale;
			$watermark->scaleImage(0, $theight);
		}

		$wWidth = $watermark->getImageWidth();
		$wHeight = $watermark->getImageHeight();

		// calculate the position
		$x = ($iWidth - $wWidth) / 2;
		$y = ($iHeight - $wHeight) / 2;

		// Set the colorspace to the same value

		$image->compositeImage($watermark, Imagick::COMPOSITE_OVER, $x, $y);
		unset($watermark);
	}

	$image->setImageFormat("jpg");

	return $image;
}

// run first time image/video is uploaded, never again unless thumbnails/flv vanish
function ss_attach_images($postid,$title=''){
	if (!($fileurl[] = ss_check_image_product($postid))) return;

    require_once(ABSPATH . '/wp-admin/includes/file.php');
    require_once(ABSPATH . '/wp-admin/includes/media.php');
    require_once(ABSPATH . '/wp-admin/includes/image.php');
    require_once(ABSPATH . '/wp-includes/pluggable.php');

	$i = 0;
	foreach ($fileurl as $orig) {
		if ($attachmentid = ss_add_featured_image($orig, $postid,$title)) set_post_thumbnail($postid, $attachmentid);
		@$i++;
	}
	return $i;
}

function ss_add_featured_image($fileurl, $postid,$title) {
	$tmpfilename = $postid."_".microtime(true).".jpg";
	if ($image = ss_make_watermark($fileurl)) {
		$image->writeImage($GLOBALS['currSS_L']->ss_tmp_dir.$tmpfilename);
		$image->clear();

		$image = $GLOBALS['currSS_L']->ss_tmp_dir.$tmpfilename;

		if (!$title) $title = get_the_title($postid);

		if (!$title) $title = pathinfo($fileurl,PATHINFO_FILENAME);
		$ftitle = $title.'.jpg';

		$blog_title = get_bloginfo();
		$file_title = $title;
		$title = $file_title." - ".$blog_title;
		$my_post = array(
	      'post_title'   => $title,
	      'post_name' => $title,
	      'post_content' => $title,
	      'post_excerpt' => $title
		);

		$array = array(
	            'name' => $ftitle,
	            'type' => 'image/jpeg', 
	            'tmp_name' => $image, 
	            'error' => 0, 
	            'size' => filesize($image) 
		);

		return media_handle_sideload($array, $postid, $title, $my_post);
	}
}

function ss_check_image_product($postid) {
	$fileurl = $GLOBALS['currSS_L']->ss_media_dir.get_post_meta($postid, 'ss_media_filename', true);

	if (!file_exists($fileurl) || is_dir($fileurl)) {
		$GLOBALS['currSS_L']->scripterr("Failed to find original media: ".pathinfo($fileurl,PATHINFO_BASENAME).", product ID: ".$postid);
		return false;
	}
	return $fileurl;
}

if (!class_exists('Imagick')) {
	global $ss_gd;
	$ss_gd = 1;

	add_filter('ss_send_processed_download','ss_gd_manage_download');
	function ss_gd_manage_download($image) {
		imagejpeg($image->im,NULL,100);
	}

	class Imagick {

		const COMPOSITE_OVER = '';
		const FILTER_LANCZOS = '';

		function Imagick($file='') {
			$this->imageloc = $file;
			return $this->readImage($this->imageloc);
		}

		function readImage($filepath) {
			if ($filepath) {
			    $type = exif_imagetype($filepath);
			    $allowedTypes = array(2,3); 
			    if (!in_array($type, $allowedTypes)) { 
			        return false; 
			    }
			    switch ($type) { 
			        case 2 : 
			            $this->im = imageCreateFromJpeg($filepath); 
			        break; 
			        case 3 : 
			            $this->im = imageCreateFromPng($filepath); 
						imagealphablending($this->im, false);
						imagesavealpha($this->im, true);
			        break; 
			    }

			    if ($this->im) return $this->im;
			}
			return false;
		}

		function getImageWidth() {
			return imagesx($this->im);
		}

		function getImageHeight() {
			return imagesy($this->im);
		}

		function resizeImage($width, $height, $filtertype='', $bluramount='') {
			if (!$width) $width = 99999999999999999999;
			elseif (!$height) $height = 99999999999999999999;

			$width_orig = $this->getImageWidth();
			$height_orig = $this->getImageHeight();

			$ratio_orig = $width_orig/$height_orig;

			if ($width/$height > $ratio_orig) {
			   $width = $height*$ratio_orig;
			} else {
			   $height = $width/$ratio_orig;
			}

			$image_p = imagecreatetruecolor($width,$height);
			imagealphablending($image_p, false);
			imagesavealpha($image_p, true);

			imagecopyresampled($image_p, $this->im, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
			$this->im = $image_p;
		}

		function scaleImage($twidth, $theight) {
			$this->resizeImage($twidth,$theight);
		}

		function setImageFormat($var) {

		}

		function thumbnailImage($width, $height, $bestfit) {
			$this->resizeImage($width,$height);
		}

		function cropThumbnailImage($width, $height) {
			$this->resizeImage($width,$height);
		}

		function setImageColorspace($var='') {

		}

		function getImageColorspace($var='') {

		}

		function compositeImage($watermark, $compoover, $x, $y) {
			imagealphablending($this->im, true);
			imagecopyresampled($this->im, $watermark->im, $x, $y, 0, 0, $watermark->getImageWidth(), $watermark->getImageHeight(), $watermark->getImageWidth(), $watermark->getImageHeight());
		}

		function writeImage($filename) {
			imagejpeg($this->im, $filename, 85);
		}

		function setImageCompressionQuality($var='') {

		}

		function clear() {
			unset($this->im);
		}
	}
}

?>