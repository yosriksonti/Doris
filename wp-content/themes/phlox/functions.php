<?php
/**
 *  Functions and definitions for auxin framework
 *
 * 
 * @package    Auxin
 * @author     averta (c) 2014-2020
 * @link       http://averta.net
 */

/*-----------------------------------------------------------------------------------*/
/*  Add your custom functions here -  We recommend you to use "code-snippets" plugin instead
/*  https://wordpress.org/plugins/code-snippets/
/*-----------------------------------------------------------------------------------*/
add_filter( 'product_type_selector', 'misha_remove_grouped_and_external' );
 
function misha_remove_grouped_and_external( $product_types ){
 
	unset( $product_types['grouped'] );
	unset( $product_types['external'] );
	
	unset( $product_types['simple'] );
	//unset( $product_types['variable'] );
 
	return $product_types;
}


add_filter('woocommerce_product_data_tabs', 'misha_product_data_tabs' );
function misha_product_data_tabs( $tabs ){
 
	unset( $tabs['inventory'] );
		unset( $tabs['advanced'] );
		unset( $tabs['shipping'] );
	return $tabs;
 
}

add_filter( 'product_type_selector', 'misha_rename_variable_type' );
 
function misha_rename_variable_type( $product_types ){
 
	$product_types['variable'] = 'Downloadable Format';
	return $product_types;
 
}

    

/*-----------------------------------------------------------------------------------*/
/*  Init theme framework
/*-----------------------------------------------------------------------------------*/
require( 'auxin/auxin-include/auxin.php' );
/*-----------------------------------------------------------------------------------*/
